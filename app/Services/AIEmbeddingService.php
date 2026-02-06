<?php

namespace App\Services;

use OpenAI\OpenAI;

class AIEmbeddingService
{
    protected OpenAI $client;

    public function __construct()
    {
        $this->client = new OpenAI(env('OPENAI_API_KEY'));
    }

    /**
     * Generate embeddings for a given text
     *
     * @param string $text
     * @return array<float>
     */
    public function generateEmbedding(string $text): array
    {
        $response = $this->client->embeddings()->create([
            'model' => 'text-embedding-3-small', // fast & cost-effective
            'input' => $text,
        ]);

        return $response->data[0]->embedding;
    }

    /**
     * Calculate cosine similarity between two vectors
     *
     * @param array<float> $vectorA
     * @param array<float> $vectorB
     * @return float
     */
    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($vectorA); $i++) {
            $dot += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] ** 2;
            $normB += $vectorB[$i] ** 2;
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0)
            return 0.0;

        return $dot / ($normA * $normB);
    }
}
