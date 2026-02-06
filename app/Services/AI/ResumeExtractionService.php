<?php

namespace App\Services\AI;
use OpenAI\Laravel\Facades\OpenAI;


class ResumeExtractionService
{
    public function extract(string $resumeText): array
    {
        $prompt = "Extract structured JSON from the resume with fields:\n" .
            "name, email, education[], skills[], certifications[], years_experience, work_history[].";


        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'Return ONLY valid JSON. No explanation.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt . "\nRESUME:\n" . $resumeText
                ],
            ],
            'response_format' => ['type' => 'json_object'],
        ]);


        return json_decode($response->choices[0]->message->content, true);
    }
}