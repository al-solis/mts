<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;

class ResumeExtractionService
{
    public function extract(string $resumeText): array
    {
        $prompt = <<<PROMPT
        Extract structured JSON from the resume with the following detailed fields:

        1. name (string)
        2. email (string or null)
        3. education (array of objects, each containing: 
        - degree (string, e.g., "Bachelor of Science")
        - field (string, e.g., "Computer Science")
        - institution (string)
        - year (string, e.g., "2015-2019")
        - gpa (optional float, if available))
        4. work_history (array of objects, each containing:
        - job_title (string)
        - company (string)
        - duration (string, e.g., "Jan 2020 - Present")
        - years (float, calculated from duration)
        - responsibilities (array of strings)
        - technologies (array of strings, extracted from responsibilities))
        5. skills (array of objects, each containing:
        - category (string, e.g., "Programming Languages", "Frameworks", "Databases", "Tools", "Project Management", 
        "Planning", "Customer Relations", "Bookkeeping", "Logistics")
        - items (array of strings, specific skills))
        6. certifications (array of strings)
        7. years_experience (float, total years from all work_history entries)
        8. soft_skills (array of strings, inferred from resume content like "leadership", "communication", "teamwork")

        Rules:
        - Return ONLY valid JSON
        - Calculate total years_experience by summing all years from work_history
        - For education: extract the highest degree level (Bachelor's, Master's, PhD, etc.)
        - For work_history: parse duration strings to calculate years (e.g., "2 years 3 months" = 2.25)
        - For skills: categorize them into logical groups
        - For responsibilities: extract key technologies mentioned
        - If a field is not found, return empty array [] or appropriate default

        RESUME TEXT:
        {$resumeText}

        Return ONLY the JSON object, no explanations.
PROMPT;

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a resume parsing expert. Extract detailed structured information and return ONLY valid JSON. Do not include any explanations, markdown, or additional text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.1, // Low temperature for consistent output
                'max_tokens' => 2000,
            ]);

            $content = $response->choices[0]->message->content;

            // Log the raw response for debugging
            Log::info('AI Resume Extraction Response', [
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 500)
            ]);

            $data = json_decode($content, true);

            // Validate and normalize the response
            return $this->normalizeExtractedData($data);

        } catch (\Throwable $e) {
            Log::error('AI Resume Extraction Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return basic structure if extraction fails
            return $this->getDefaultStructure($resumeText);
        }
    }

    private function normalizeExtractedData(array $data): array
    {
        // Ensure all required fields exist with defaults
        $defaults = [
            'name' => 'Unknown Applicant',
            'email' => null,
            'education' => [],
            'work_history' => [],
            'skills' => [],
            'certifications' => [],
            'years_experience' => 0,
            'soft_skills' => [],
        ];

        $data = array_merge($defaults, $data);

        // Normalize education array
        $data['education'] = $this->normalizeEducation($data['education']);

        // Normalize work_history array
        $data['work_history'] = $this->normalizeWorkHistory($data['work_history']);

        // Normalize skills array
        $data['skills'] = $this->normalizeSkills($data['skills']);

        // Normalize certifications array
        $data['certifications'] = $this->normalizeCertifications($data['certifications']);

        // Normalize soft_skills array
        $data['soft_skills'] = $this->normalizeArray($data['soft_skills']);

        // Ensure years_experience is numeric
        $data['years_experience'] = floatval($data['years_experience']);

        // If years_experience is 0 but we have work_history, calculate it
        if ($data['years_experience'] == 0 && !empty($data['work_history'])) {
            $data['years_experience'] = $this->calculateTotalExperience($data['work_history']);
        }

        return $data;
    }

    private function normalizeEducation($input): array
    {
        $result = [];

        if (!is_array($input)) {
            return $result;
        }

        foreach ($input as $item) {
            if (is_array($item)) {
                $education = [
                    'degree' => $item['degree'] ?? '',
                    'field' => $item['field'] ?? '',
                    'institution' => $item['institution'] ?? '',
                    'year' => $item['year'] ?? '',
                    'gpa' => isset($item['gpa']) ? floatval($item['gpa']) : null,
                ];

                // Only add if we have at least a degree or field
                if (!empty($education['degree']) || !empty($education['field'])) {
                    $result[] = $education;
                }
            } elseif (is_string($item) && !empty(trim($item))) {
                // Handle simple string entries
                $result[] = [
                    'degree' => $item,
                    'field' => '',
                    'institution' => '',
                    'year' => '',
                    'gpa' => null,
                ];
            }
        }

        return $result;
    }

    private function normalizeWorkHistory($input): array
    {
        $result = [];

        if (!is_array($input)) {
            return $result;
        }

        foreach ($input as $item) {
            if (is_array($item)) {
                $work = [
                    'job_title' => $item['job_title'] ?? '',
                    'company' => $item['company'] ?? '',
                    'duration' => $item['duration'] ?? '',
                    'years' => isset($item['years']) ? floatval($item['years']) : $this->parseDurationToYears($item['duration'] ?? ''),
                    'responsibilities' => $this->normalizeArray($item['responsibilities'] ?? []),
                    'technologies' => $this->normalizeArray($item['technologies'] ?? []),
                ];

                // Only add if we have at least a job title
                if (!empty($work['job_title'])) {
                    $result[] = $work;
                }
            } elseif (is_string($item) && !empty(trim($item))) {
                // Handle simple string entries
                $result[] = [
                    'job_title' => $item,
                    'company' => '',
                    'duration' => '',
                    'years' => 0,
                    'responsibilities' => [],
                    'technologies' => [],
                ];
            }
        }

        return $result;
    }

    private function normalizeSkills($input): array
    {
        $result = [];

        if (!is_array($input)) {
            return $result;
        }

        // Handle structured skills (with categories)
        foreach ($input as $item) {
            if (is_array($item) && isset($item['category'])) {
                $category = trim($item['category']);
                $items = $this->normalizeArray($item['items'] ?? []);

                if (!empty($category) && !empty($items)) {
                    $result[] = [
                        'category' => $category,
                        'items' => $items,
                    ];
                }
            } elseif (is_string($item) && !empty(trim($item))) {
                // Handle simple skill strings by categorizing them
                $skill = trim($item);
                $result[] = [
                    'category' => 'General Skills',
                    'items' => [$skill],
                ];
            }
        }

        // If no structured skills, try to extract from simple array
        if (empty($result) && is_array($input)) {
            $skills = [];
            foreach ($input as $item) {
                if (is_string($item) && !empty(trim($item))) {
                    $skills[] = trim($item);
                }
            }

            if (!empty($skills)) {
                $result[] = [
                    'category' => 'General Skills',
                    'items' => $skills,
                ];
            }
        }

        return $result;
    }

    private function normalizeCertifications($input): array
    {
        $result = [];

        if (!is_array($input)) {
            return $result;
        }

        foreach ($input as $item) {
            if (is_string($item) && !empty(trim($item))) {
                $result[] = trim($item);
            } elseif (is_array($item)) {
                // Try to find certification strings in arrays
                foreach ($item as $key => $value) {
                    if (is_string($value) && !empty(trim($value))) {
                        if (
                            stripos($value, 'certified') !== false ||
                            stripos($value, 'certification') !== false ||
                            strlen($value) < 100
                        ) { // Likely a certification name
                            $result[] = trim($value);
                        }
                    }
                }
            }
        }

        return array_unique($result);
    }

    private function normalizeArray($input): array
    {
        $result = [];

        if (!is_array($input)) {
            return $result;
        }

        foreach ($input as $item) {
            if (is_string($item) && !empty(trim($item))) {
                $result[] = trim($item);
            }
        }

        return array_unique($result);
    }

    private function parseDurationToYears(string $duration): float
    {
        // Try to parse duration strings like "2 years 3 months", "2018-2020", etc.
        $years = 0;

        // Pattern for "X years Y months"
        if (preg_match('/(\d+)\s*years?/', $duration, $matches)) {
            $years += intval($matches[1]);
        }

        if (preg_match('/(\d+)\s*months?/', $duration, $matches)) {
            $years += intval($matches[1]) / 12;
        }

        // Pattern for date range "YYYY - YYYY"
        if (preg_match('/(\d{4})\s*[-–]\s*(\d{4})/', $duration, $matches)) {
            $start = intval($matches[1]);
            $end = intval($matches[2]);
            $years = $end - $start;
        }

        // Pattern for date range "Month YYYY - Month YYYY"
        if (preg_match('/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}\s*[-–]\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+\d{4}/i', $duration)) {
            // Simple approximation: assume each job is at least 1 year
            $years = max($years, 1);
        }

        return round($years, 2);
    }

    private function calculateTotalExperience(array $workHistory): float
    {
        $totalYears = 0;

        foreach ($workHistory as $job) {
            if (is_array($job) && isset($job['years'])) {
                $totalYears += floatval($job['years']);
            }
        }

        return round($totalYears, 2);
    }

    private function getDefaultStructure(string $text): array
    {
        // Basic parsing as fallback
        $lines = explode("\n", $text);

        return [
            'name' => !empty($lines[0]) && strlen(trim($lines[0])) < 100 ? trim($lines[0]) : 'Unknown Applicant',
            'email' => $this->extractEmail($text),
            'education' => [],
            'work_history' => [],
            'skills' => $this->extractBasicSkills($text),
            'certifications' => [],
            'years_experience' => $this->extractExperienceFromText($text),
            'soft_skills' => [],
        ];
    }

    private function extractEmail(string $text): ?string
    {
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function extractBasicSkills(string $text): array
    {
        $commonSkills = [
            'Programming Languages' => ['php', 'javascript', 'python', 'java', 'c++', 'c#', 'ruby', 'go', 'swift', 'kotlin'],
            'Web Development' => ['html', 'css', 'react', 'vue', 'angular', 'laravel', 'django', 'node.js', 'express', 'spring'],
            'Databases' => ['mysql', 'postgresql', 'mongodb', 'redis', 'sqlite', 'oracle'],
            'Tools' => ['git', 'docker', 'aws', 'azure', 'jenkins', 'kubernetes'],
            'Soft Skills' => ['leadership', 'communication', 'teamwork', 'problem solving', 'time management'],
        ];

        $foundSkills = [];
        $textLower = strtolower($text);

        foreach ($commonSkills as $category => $skills) {
            $categorySkills = [];
            foreach ($skills as $skill) {
                if (stripos($textLower, $skill) !== false) {
                    $categorySkills[] = ucwords($skill);
                }
            }

            if (!empty($categorySkills)) {
                $foundSkills[] = [
                    'category' => $category,
                    'items' => $categorySkills,
                ];
            }
        }

        return $foundSkills;
    }

    private function extractExperienceFromText(string $text): float
    {
        // Try to find experience patterns
        if (preg_match('/(\d+)\+?\s*(?:years?|yrs?)\s+(?:of\s+)?experience/i', $text, $matches)) {
            return floatval($matches[1]);
        }

        if (preg_match('/experience\s*:?\s*(\d+)\+?\s*(?:years?|yrs?)/i', $text, $matches)) {
            return floatval($matches[1]);
        }

        return 0;
    }
}