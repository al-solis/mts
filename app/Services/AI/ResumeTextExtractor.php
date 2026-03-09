<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ResumeTextExtractor
{
    public function extract($file): string
    {
        // Normalize the path if it's a string
        if (is_string($file)) {
            $file = $this->normalizePath($file);
            if (!file_exists($file)) {
                Log::error('File does not exist in extractor', ['path' => $file]);
                return '';
            }

            Log::info('Extractor: File exists', [
                'path' => $file,
                'size' => filesize($file),
                'readable' => is_readable($file) ? 'yes' : 'no'
            ]);

            $cacheKey = 'resume_text_' . md5($file . filesize($file));
            return Cache::remember($cacheKey, 3600, function () use ($file) {
                return $this->extractTextFromPath($file);
            });
        }

        // If it's an UploadedFile object
        $cacheKey = 'resume_text_' . md5($file->getRealPath() . $file->getSize());
        return Cache::remember($cacheKey, 3600, function () use ($file) {
            return $this->extractText($file);
        });
    }

    protected function extractText($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $this->normalizePath($file->getRealPath());

        return $this->extractByExtension($path, $extension);
    }

    protected function extractTextFromPath(string $path): string
    {
        $path = $this->normalizePath($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        Log::info('Extracting text from path', [
            'path' => $path,
            'extension' => $extension,
            'file_exists' => file_exists($path) ? 'yes' : 'no'
        ]);

        return $this->extractByExtension($path, $extension);
    }

    protected function extractByExtension(string $path, string $extension): string
    {
        if (!file_exists($path)) {
            Log::error('File not found in extractByExtension', ['path' => $path]);
            return '';
        }

        try {
            Log::info('Starting extraction for file', [
                'path' => $path,
                'extension' => $extension,
                'size' => filesize($path)
            ]);

            // Use fastest method based on file type
            $result = match ($extension) {
                'pdf' => $this->extractFromPdf($path),
                'docx' => $this->extractFromDocxFast($path),
                'doc' => $this->extractFromDocFast($path),
                'txt' => file_get_contents($path),
                default => ''
            };

            Log::info('Extraction completed', [
                'path' => $path,
                'result_length' => strlen($result),
                'result_preview' => substr($result, 0, 100)
            ]);

            return $result;

        } catch (\Throwable $e) {
            Log::error('Extraction failed', [
                'error' => $e->getMessage(),
                'path' => $path,
                'extension' => $extension,
                'trace' => $e->getTraceAsString()
            ]);
            return '';
        }
    }

    protected function extractFromPdf(string $path): string
    {
        $path = $this->normalizePath($path);

        // Try pdftotext first (much faster than PDF Parser)
        if ($this->commandExists('pdftotext')) {
            Log::info('Using pdftotext for PDF extraction');
            $output = shell_exec("pdftotext -layout " . escapeshellarg($path) . " - 2>&1");
            if (!empty(trim($output))) {
                return $output;
            }
        }

        // Fallback to PDF Parser
        try {
            Log::info('Using PDF Parser fallback');
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = trim($pdf->getText());
            Log::info('PDF Parser result', ['length' => strlen($text)]);
            return $text;
        } catch (\Exception $e) {
            Log::error('PDF Parser failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    protected function extractFromDocxFast(string $path): string
    {
        $path = $this->normalizePath($path);

        // Direct XML extraction (fastest)
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xml = $zip->getFromIndex($index);
                $zip->close();

                // Strip XML tags efficiently
                $text = strip_tags($xml);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
                return trim(preg_replace('/\s+/', ' ', $text));
            }
            $zip->close();
        }
        return '';
    }

    protected function extractFromDocFast(string $path): string
    {
        $path = $this->normalizePath($path);

        if ($this->commandExists('catdoc')) {
            return shell_exec('catdoc ' . escapeshellarg($path) . ' 2>&1') ?? '';
        }
        return '';
    }

    protected function commandExists(string $command): bool
    {
        $which = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
        return !empty(shell_exec("$which $command 2>/dev/null"));
    }

    /**
     * Normalize path for Windows
     */
    private function normalizePath(string $path): string
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}