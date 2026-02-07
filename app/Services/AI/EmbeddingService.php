<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    public function embed(string $text): array
    {
        try {
            Log::info('Starting embedding generation', [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100)
            ]);

            // Truncate text if too long (OpenAI has token limits)
            $truncatedText = $this->truncateText($text, 8000);

            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $truncatedText,
            ]);

            // Log the response structure for debugging
            Log::info('OpenAI Embedding Response', [
                'response_class' => get_class($response),
                'response_methods' => array_filter(get_class_methods($response), function ($method) {
                    return !str_starts_with($method, '__');
                })
            ]);

            // Convert response to array and check structure
            $responseArray = $response->toArray();
            Log::info('OpenAI Response Array Structure', array_keys($responseArray));

            // Try different ways to access the embedding
            if (isset($responseArray['data'][0]['embedding'])) {
                Log::info('Found embedding in data[0][embedding]');
                return $responseArray['data'][0]['embedding'];
            } elseif (isset($responseArray[0]['embedding'])) {
                Log::info('Found embedding in [0][embedding]');
                return $responseArray[0]['embedding'];
            } elseif (isset($responseArray['embedding'])) {
                Log::info('Found embedding in [embedding]');
                return $responseArray['embedding'];
            }

            // Check if there's an embeddings method
            if (method_exists($response, 'embeddings')) {
                $embeddings = $response->embeddings();
                if (!empty($embeddings)) {
                    Log::info('Found embedding via embeddings() method');
                    return $embeddings[0]->embedding;
                }
            }

            // If we get here, log the full response to debug
            Log::warning('Could not find embedding, full response:', $responseArray);
            throw new \Exception('Unable to extract embedding from OpenAI response');

        } catch (\Throwable $e) {
            Log::error('Embedding generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a zero vector as fallback (text-embedding-3-small has 1536 dimensions)
            return array_fill(0, 1536, 0);
        }
    }

    private function truncateText(string $text, int $maxLength = 8000): string
    {
        if (strlen($text) <= $maxLength) {
            return $text;
        }

        // Truncate to max length while trying to end at a word boundary
        $truncated = substr($text, 0, $maxLength);
        $lastSpace = strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }

        return $truncated . '... [truncated]';
    }
}