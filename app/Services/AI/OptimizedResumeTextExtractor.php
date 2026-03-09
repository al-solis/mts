<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OptimizedResumeTextExtractor
{
    // Cache extracted text to avoid reprocessing
    public function extract($file): string
    {
        $cacheKey = 'resume_text_' . md5($file->getRealPath() . $file->getSize());

        return Cache::remember($cacheKey, 3600, function () use ($file) {
            return $this->extractText($file);
        });
    }

    protected function extractText($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            // Use fastest method based on file type
            return match ($extension) {
                'pdf' => $this->extractFromPdf($path),
                'docx' => $this->extractFromDocxFast($path),
                'doc' => $this->extractFromDocFast($path),
                'txt' => file_get_contents($path),
                default => ''
            };
        } catch (\Throwable $e) {
            Log::error('Extraction failed', ['error' => $e->getMessage()]);
            return '';
        }
    }

    protected function extractFromPdf(string $path): string
    {
        // Try pdftotext first (much faster than PDF Parser)
        if ($this->commandExists('pdftotext')) {
            $output = shell_exec("pdftotext -layout " . escapeshellarg($path) . " - 2>/dev/null");
            if (!empty(trim($output))) {
                return $output;
            }
        }

        // Fallback to PDF Parser
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);
        return trim($pdf->getText());
    }

    protected function extractFromDocxFast(string $path): string
    {
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
        if ($this->commandExists('catdoc')) {
            return shell_exec('catdoc ' . escapeshellarg($path) . ' 2>/dev/null') ?? '';
        }
        return '';
    }

    protected function commandExists(string $command): bool
    {
        $which = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'which';
        return !empty(shell_exec("$which $command 2>/dev/null"));
    }
}