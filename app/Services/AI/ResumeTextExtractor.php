<?php

namespace App\Services\AI;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;
use Spatie\PdfToImage\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Section;

class ResumeTextExtractor
{
    public function extract($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            Log::info('Processing file', [
                'file' => $file->getClientOriginalName(),
                'extension' => $extension,
                'size' => $file->getSize()
            ]);

            // ================= PDF =================
            if ($extension === 'pdf') {
                return $this->extractFromPdf($path);
            }

            // ================= DOC / DOCX =================
            if (in_array($extension, ['doc', 'docx'])) {
                return $this->extractFromDocx($path);
            }

            // ================= TXT =================
            if ($extension === 'txt') {
                $content = file_get_contents($path);
                return trim($content);
            }

        } catch (\Throwable $e) {
            Log::error('Resume extraction failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return '';
    }

    // ================= PDF EXTRACTION =================
    protected function extractFromPdf(string $path): string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = trim($pdf->getText());

            // If we got reasonable text, return it
            if (strlen($text) > 50) {
                Log::info('PDF text extraction successful', [
                    'text_length' => strlen($text)
                ]);
                return $text;
            }

            // Fallback to OCR for scanned PDFs
            Log::info('PDF text too short, trying OCR fallback');
            return $this->ocrPdf($path);

        } catch (\Throwable $e) {
            Log::error('PDF extraction failed', [
                'error' => $e->getMessage()
            ]);
            return '';
        }
    }

    // ================= DOCX EXTRACTION (IMPROVED) =================
    protected function extractFromDocx(string $path): string
    {
        try {
            Log::info('Starting DOCX extraction');

            // Check if file exists and is readable
            if (!file_exists($path)) {
                Log::error('DOCX file not found', ['path' => $path]);
                return '';
            }

            // Try multiple methods to extract text
            $text = $this->tryDocxMethods($path);

            if (empty(trim($text))) {
                Log::warning('DOCX extraction returned empty, trying alternative methods');

                // Method 3: Try reading as XML (simple approach)
                $text = $this->extractDocxAsXml($path);

                if (empty(trim($text))) {
                    // Method 4: Use shell command with docx2txt if available
                    $text = $this->extractDocxViaCommand($path);
                }
            }

            $text = trim($text);
            Log::info('DOCX extraction completed', [
                'text_length' => strlen($text),
                'preview' => substr($text, 0, 100) . '...'
            ]);

            return $text;

        } catch (\Throwable $e) {
            Log::error('DOCX extraction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }

    protected function tryDocxMethods(string $path): string
    {
        $text = '';

        // Method 1: Using PhpWord
        try {
            $phpWord = IOFactory::load($path);

            foreach ($phpWord->getSections() as $section) {
                $text .= $this->extractTextFromElement($section);
            }

            if (!empty(trim($text))) {
                Log::info('PhpWord extraction successful');
                return $text;
            }
        } catch (\Throwable $e) {
            Log::warning('PhpWord method failed', ['error' => $e->getMessage()]);
        }

        // Method 2: Simple XML parsing for .docx (zip archive)
        if (empty(trim($text))) {
            $text = $this->extractDocxAsXml($path);
        }

        return $text;
    }

    protected function extractTextFromElement($element): string
    {
        $text = '';

        if ($element instanceof Text) {
            $text .= $element->getText() . ' ';
        } elseif ($element instanceof TextRun) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractTextFromElement($child);
            }
        } elseif (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractTextFromElement($child);
            }
        } elseif (method_exists($element, 'getText')) {
            $text .= $element->getText() . ' ';
        }

        return $text;
    }

    protected function extractDocxAsXml(string $path): string
    {
        try {
            $text = '';

            // DOCX is a ZIP archive containing XML files
            $zip = new \ZipArchive();

            if ($zip->open($path) === TRUE) {
                // Look for document.xml in the archive
                if (($index = $zip->locateName("word/document.xml")) !== false) {
                    $xmlContent = $zip->getFromIndex($index);

                    // Remove XML tags and clean up
                    $text = strip_tags($xmlContent);
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                }

                $zip->close();
            }

            return trim($text);

        } catch (\Throwable $e) {
            Log::warning('XML extraction failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    protected function extractDocxViaCommand(string $path): string
    {
        try {
            // Check if antiword or docx2txt is available
            $commands = [
                'docx2txt' => "docx2txt \"$path\" -",
                'unzip' => "unzip -p \"$path\" word/document.xml | sed -e 's/<[^>]*>/ /g'",
                'pandoc' => "pandoc \"$path\" -t plain"
            ];

            foreach ($commands as $cmd => $command) {
                if ($this->commandExists($cmd)) {
                    $output = shell_exec($command);
                    if (!empty(trim($output))) {
                        return trim($output);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Command extraction failed', ['error' => $e->getMessage()]);
        }

        return '';
    }

    protected function commandExists(string $command): bool
    {
        $which = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
        return shell_exec("$which $command") !== null;
    }

    // ================= OCR =================
    protected function ocrPdf(string $path): string
    {
        try {
            Log::info('Starting OCR processing');

            $pdf = new Pdf($path);
            $pdf->setResolution(150); // Lower resolution for faster processing

            $tempDir = storage_path('app/ocr');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $text = '';
            $pageCount = min($pdf->getNumberOfPages(), 5); // Limit to 5 pages for performance

            for ($page = 1; $page <= $pageCount; $page++) {
                $imagePath = $tempDir . "/page_{$page}_" . time() . ".jpg";

                try {
                    $pdf->setPage($page)->saveImage($imagePath);

                    $ocr = new TesseractOCR($imagePath);
                    $ocr->lang('eng');
                    $pageText = $ocr->run();

                    if (!empty(trim($pageText))) {
                        $text .= $pageText . "\n\n";
                    }

                    // Clean up temp file
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }

                } catch (\Throwable $e) {
                    Log::warning("OCR failed for page $page", ['error' => $e->getMessage()]);
                    continue;
                }
            }

            Log::info('OCR completed', [
                'pages_processed' => $pageCount,
                'text_length' => strlen($text)
            ]);

            return trim($text);

        } catch (\Throwable $e) {
            Log::error('OCR processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }
}