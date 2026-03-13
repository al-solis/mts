<?php

namespace App\Services\AI;

use ZipArchive;
use Smalot\PdfParser\Parser as PdfParser;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Log;
use Imagick;

class ResumePhotoExtractor
{
    /**
     * Extract photo from resume
     *
     * @param string $path Full path to resume file
     * @return string|null Relative path to saved photo or null if none
     */
    public function extract(string $path): ?string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'docx':
                return $this->extractFromDocx($path);

            case 'pdf':
                return $this->extractFromPdf($path);

            case 'doc':
                return $this->extractFromDoc($path);

            default:
                Log::warning('Unsupported resume format for photo extraction', [
                    'file' => $path,
                    'extension' => $extension
                ]);
                return null;
        }
    }

    /**
     * Extract images from DOCX
     */
    private function extractFromDocx(string $path): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            Log::error('Failed to open DOCX file', ['file' => $path]);
            return null;
        }

        $bestImage = null;
        $bestScore = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);

            if (!str_contains($fileName, 'word/media/'))
                continue;

            $imageData = $zip->getFromIndex($i);
            $info = @getimagesizefromstring($imageData);
            if (!$info)
                continue;

            $width = $info[0];
            $height = $info[1];
            $area = $width * $height;
            if ($area < 5000)
                continue; // skip tiny icons

            $ratio = $width / $height;
            $score = $area * (1 - abs(1 - $ratio)); // prefer square images

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestImage = $imageData;
            }
        }

        $zip->close();

        if (!$bestImage)
            return null;

        return $this->saveImage($bestImage);
    }

    /**
     * Extract images from PDF using multiple methods
     */
    private function extractFromPdf(string $path): ?string
    {
        // Try method 1: spatie/pdf-to-image (requires ImageMagick)
        $photo = $this->extractFromPdfViaSpatie($path);
        if ($photo) {
            return $photo;
        }

        // Try method 2: direct Imagick (if available)
        $photo = $this->extractFromPdfViaImagick($path);
        if ($photo) {
            return $photo;
        }

        // Try method 3: look for embedded images using PDF parser
        $photo = $this->extractEmbeddedImagesFromPdf($path);
        if ($photo) {
            return $photo;
        }

        Log::warning('All PDF photo extraction methods failed', ['file' => $path]);
        return null;
    }

    /**
     * Extract using spatie/pdf-to-image
     */
    private function extractFromPdfViaSpatie(string $path): ?string
    {
        try {
            if (!class_exists('\Spatie\PdfToImage\Pdf')) {
                Log::warning('Spatie PdfToImage not available');
                return null;
            }

            $pdf = new Pdf($path);

            // Check if the PDF has any pages
            $pageCount = $pdf->getNumberOfPages();
            if ($pageCount === 0) {
                Log::warning('PDF has no pages', ['file' => $path]);
                return null;
            }

            $pdf->setPage(1); // first page only

            // Set output format
            $pdf->setOutputFormat('jpg');

            $filename = 'photos/' . uniqid() . '.jpg';
            $fullPath = storage_path('app/public/' . $filename);

            // ensure folder exists
            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $pdf->saveImage($fullPath);

            // Verify the image was created and has content
            if (!file_exists($fullPath) || filesize($fullPath) < 1000) {
                Log::warning('PDF to image conversion produced invalid image', [
                    'file' => $path,
                    'output' => $fullPath,
                    'exists' => file_exists($fullPath) ? 'yes' : 'no',
                    'size' => file_exists($fullPath) ? filesize($fullPath) : 0
                ]);

                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                return null;
            }

            Log::info('PDF first page saved as image (via Spatie)', [
                'file' => $path,
                'photo_path' => $filename,
                'image_size' => filesize($fullPath)
            ]);

            return $filename;

        } catch (\Throwable $e) {
            Log::warning('Spatie PDF photo extraction failed', [
                'file' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract using direct Imagick
     */
    private function extractFromPdfViaImagick(string $path): ?string
    {
        try {
            if (!extension_loaded('imagick')) {
                Log::warning('Imagick extension not loaded');
                return null;
            }

            $imagick = new Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($path . '[0]'); // Read first page only
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(85);

            $filename = 'photos/' . uniqid() . '.jpg';
            $fullPath = storage_path('app/public/' . $filename);

            // ensure folder exists
            if (!is_dir(dirname($fullPath))) {
                mkdir(dirname($fullPath), 0755, true);
            }

            $imagick->writeImage($fullPath);
            $imagick->clear();
            $imagick->destroy();

            // Verify the image was created
            if (!file_exists($fullPath) || filesize($fullPath) < 1000) {
                Log::warning('Imagick PDF conversion produced invalid image', ['file' => $path]);
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                return null;
            }

            Log::info('PDF first page saved as image (via Imagick)', [
                'file' => $path,
                'photo_path' => $filename
            ]);

            return $filename;

        } catch (\Throwable $e) {
            Log::warning('Imagick PDF photo extraction failed', [
                'file' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Extract embedded images directly from PDF using PDF Parser
     */
    private function extractEmbeddedImagesFromPdf(string $path): ?string
    {
        try {
            if (!class_exists('\Smalot\PdfParser\Parser')) {
                Log::warning('Smalot PDF Parser not available');
                return null;
            }

            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);

            // Get all pages
            $pages = $pdf->getPages();
            if (empty($pages)) {
                return null;
            }

            // Check first page for images
            $firstPage = $pages[0];

            // Try to get images from the page (if supported by the parser)
            if (method_exists($firstPage, 'getImages')) {
                $images = $firstPage->getImages();

                if (!empty($images)) {
                    // Find the largest image (likely the photo)
                    $bestImage = null;
                    $bestSize = 0;

                    foreach ($images as $image) {
                        if (method_exists($image, 'getData')) {
                            $data = $image->getData();
                            $size = strlen($data);

                            if ($size > $bestSize && $size > 5000) {
                                $bestSize = $size;
                                $bestImage = $data;
                            }
                        }
                    }

                    if ($bestImage) {
                        $savedPath = $this->saveImage($bestImage);
                        if ($savedPath) {
                            Log::info('Embedded image extracted from PDF', [
                                'file' => $path,
                                'photo_path' => $savedPath,
                                'image_size' => $bestSize
                            ]);
                            return $savedPath;
                        }
                    }
                }
            }

            return null;

        } catch (\Throwable $e) {
            Log::warning('PDF embedded image extraction failed', [
                'file' => $path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle old DOC files (optional)
     */
    private function extractFromDoc(string $path): ?string
    {
        // Old binary .doc files are complex, images hard to extract in pure PHP
        // Optional: convert to docx using PhpWord or skip
        Log::info('Skipping DOC file photo extraction', ['file' => $path]);
        return null;
    }

    /**
     * Save image to storage and return relative path
     */
    private function saveImage(string $data): ?string
    {
        $filename = 'photos/' . uniqid() . '.jpg';
        $fullPath = storage_path('app/public/' . $filename);

        // Ensure folder exists
        if (!is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $written = @file_put_contents($fullPath, $data);
        if ($written === false) {
            Log::error('Failed to save photo', ['path' => $fullPath]);
            return null;
        }

        Log::info('Photo saved successfully', [
            'file' => $filename,
            'full_path' => $fullPath,
            'exists' => file_exists($fullPath) ? 'yes' : 'no',
            'size' => $written
        ]);

        return $filename;
    }
}