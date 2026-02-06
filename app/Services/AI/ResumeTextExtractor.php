<?php

namespace App\Services\AI;

use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class ResumeTextExtractor
{
    public function extract($file): string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        return match ($ext) {
            'txt' => $this->fromTxt($path),
            'docx' => $this->fromDocx($path),
            'doc' => $this->fromDoc($path),
            'pdf' => $this->fromPdf($path),
            default => throw new \Exception('Unsupported resume format'),
        };
    }

    private function fromTxt($path): string
    {
        return file_get_contents($path);
    }

    private function fromDocx($path): string
    {
        $phpWord = IOFactory::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }

        return $text;
    }

    /**
     * DOC (legacy Word) – basic support
     */
    private function fromDoc($path): string
    {
        return shell_exec("antiword " . escapeshellarg($path)) ?? '';
    }

    private function fromPdf($path): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        return $pdf->getText();
    }
}
