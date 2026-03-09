<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\OptimizedResumeTextExtractor;
use App\Models\JobPosting;
use App\Models\Setting;
use App\Models\Resume;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Maximum number of attempts
    public $tries = 3;

    // Timeout in seconds
    public $timeout = 300;

    // Backoff between retries (seconds)
    public $backoff = [5, 10, 30];

    protected $filePath;
    protected $fileName;
    protected $jobId;
    protected $userId;
    protected $settings;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, string $fileName, int $jobId, int $userId, ?Setting $settings = null)
    {
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->jobId = $jobId;
        $this->userId = $userId;
        $this->settings = $settings;

        // Store settings as array if needed
        if ($settings) {
            $this->settings = [
                'education' => $settings->education ?? 25,
                'years_of_experience' => $settings->years_of_experience ?? 25,
                'work_experience_relevance' => $settings->work_experience_relevance ?? 25,
                'general' => $settings->general ?? 25,
                'minimum_match_percentage' => $settings->minimum_match_percentage ?? 70,
                'education_degree_weight' => $settings->education_degree_weight ?? 40,
                'education_field_weight' => $settings->education_field_weight ?? 40,
                'education_honors_weight' => $settings->education_honors_weight ?? 20,
            ];
        }
    }

    /**
     * Execute the job.
     */
    public function handle(
        ResumeExtractionService $aiExtractor,
        EmbeddingService $embedder,
        OptimizedResumeTextExtractor $textExtractor
    ): void {
        Log::info('Starting resume processing job', [
            'file' => $this->fileName,
            'job_id' => $this->jobId
        ]);

        try {
            // Get the file from temporary storage
            if (!file_exists($this->filePath)) {
                throw new \Exception("File not found: {$this->filePath}");
            }

            // Create a file instance from the path
            $file = new \Illuminate\Http\File($this->filePath);

            // Load job and settings
            $job = JobPosting::with('company')->findOrFail($this->jobId);

            // Get settings from database if not provided in constructor
            if (!$this->settings) {
                $settings = Setting::first();
            } else {
                $settings = (object) $this->settings;
            }

            // Extract text from resume
            $text = $textExtractor->extract($file);

            if (empty(trim($text))) {
                throw new \Exception('Text extraction returned empty content');
            }

            Log::info('Text extracted successfully', [
                'file' => $this->fileName,
                'text_length' => strlen($text)
            ]);

            // Extract structured data using AI
            $data = $aiExtractor->extract($text);

            // Calculate passing threshold
            $passingThreshold = $this->getPassingThreshold($job, $settings);

            // Calculate match percentages
            $matchResult = $this->calculateMatch($data, $job, $settings);

            // Generate embedding
            $embedding = $embedder->embed($text);

            // Save to database
            $resume = Resume::create([
                'job_posting_id' => $job->id,
                'applicant_name' => $data['name'] ?? 'Unknown Applicant',
                'email' => $data['email'] ?? null,
                'years_experience' => $data['years_experience'] ?? 0,
                'education' => json_encode($data['education'] ?? []),
                'skills' => json_encode($data['skills'] ?? []),
                'certifications' => json_encode($data['certifications'] ?? []),
                'work_history' => json_encode($data['work_history'] ?? []),
                'soft_skills' => json_encode($data['soft_skills'] ?? []),
                'raw_text' => $text,
                'embedding' => json_encode($embedding),
                'education_percentage' => $matchResult['education_percentage'] ?? 0,
                'experience_percentage' => $matchResult['experience_percentage'] ?? 0,
                'skills_percentage' => $matchResult['skills_percentage'] ?? 0,
                'certifications_percentage' => $matchResult['certifications_percentage'] ?? 0,
                'relevance_percentage' => $matchResult['relevance_percentage'] ?? 0,
                'general_percentage' => $matchResult['general_percentage'] ?? 0,
                'match_percentage' => $matchResult['total_percentage'] ?? 0,
                'status' => ($matchResult['total_percentage'] ?? 0) >= $passingThreshold ? 'Passed' : 'Failed',
                'tag' => 0, // pending
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);

            Log::info('Resume processing completed successfully', [
                'file' => $this->fileName,
                'resume_id' => $resume->id,
                'applicant' => $resume->applicant_name,
                'match_percentage' => $resume->match_percentage
            ]);

            // Clean up temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

        } catch (Throwable $e) {
            Log::error('Resume processing job failed', [
                'file' => $this->fileName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Resume processing job permanently failed', [
            'file' => $this->fileName,
            'job_id' => $this->jobId,
            'error' => $exception->getMessage()
        ]);

        // Clean up temporary file if it exists
        if (isset($this->filePath) && file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }

    /**
     * Calculate match percentages between resume and job
     */
    private function calculateMatch($data, $job, $settings): array
    {
        // Calculate each component with their respective weights
        $educationScore = $this->matchEducation($data['education'] ?? [], $job->qualification, $settings);
        $experienceScore = $this->matchExperience($data['years_experience'] ?? 0, $job);
        $relevanceScore = $this->matchWorkExperienceRelevance($data['work_history'] ?? [], $job);
        $generalScore = $this->matchGeneralQualifications($data, $job);

        // Apply weights from settings
        $total = (
            ($educationScore * ($settings->education / 100)) +
            ($experienceScore * ($settings->years_of_experience / 100)) +
            ($relevanceScore * ($settings->work_experience_relevance / 100)) +
            ($generalScore * ($settings->general / 100))
        );

        return [
            'education_percentage' => $educationScore,
            'experience_percentage' => $experienceScore,
            'relevance_percentage' => $relevanceScore,
            'general_percentage' => $generalScore,
            'skills_percentage' => $generalScore * 0.5, // Simplified
            'certifications_percentage' => $generalScore * 0.3, // Simplified
            'total_percentage' => $total
        ];
    }

    /**
     * Match education requirements
     */
    private function matchEducation($resumeEducation, $jobQualification, $settings): float
    {
        // If settings not provided, get from database or use defaults
        if (!$settings) {
            $settings = Setting::first();
        }

        $degreeWeight = $settings->education_degree_weight ?? 40;
        $fieldWeight = $settings->education_field_weight ?? 40;
        $honorsWeight = $settings->education_honors_weight ?? 20;

        // Parse job qualification for education requirements
        $jobQualification = strtolower($jobQualification);

        // Define degree levels and their scores
        $degreeLevels = [
            'phd' => 100,
            'doctorate' => 100,
            'master' => 90,
            'masters' => 90,
            'mba' => 90,
            'bachelor' => 80,
            'bachelors' => 80,
            'bs' => 80,
            'ba' => 80,
            'associate' => 70,
            'diploma' => 60,
            'certificate' => 50,
            'high school' => 40,
            'secondary' => 40,
            'primary' => 20,
            'elementary' => 20,
            'none' => 0,
        ];

        // Define field categories and their related keywords
        $fieldCategories = [
            'computer science' => ['computer science', 'cs', 'software', 'programming', 'development', 'coding'],
            'information technology' => ['information technology', 'it', 'information systems', 'networking'],
            'engineering' => ['engineering', 'engineer', 'mechanical', 'electrical', 'civil', 'chemical'],
            'business' => ['business', 'administration', 'management', 'commerce', 'finance', 'marketing'],
            'science' => ['science', 'scientific', 'biology', 'chemistry', 'physics', 'mathematics'],
            'healthcare' => ['healthcare', 'medical', 'nursing', 'medicine', 'health', 'clinical'],
            'arts' => ['arts', 'humanities', 'design', 'creative', 'graphic'],
        ];

        // Academic honors keywords with scores
        $honorsKeywords = [
            'cum laude' => 90,
            'magna cum laude' => 95,
            'summa cum laude' => 100,
            'with honors' => 85,
            'with distinction' => 85,
            'with high honors' => 90,
            'with highest honors' => 100,
            'valedictorian' => 100,
            'salutatorian' => 95,
            'dean\'s list' => 80,
            'honor roll' => 75,
            'scholarship' => 70,
            'award' => 65,
            'excellence' => 70,
        ];

        $bestMatch = 0;

        foreach ($resumeEducation as $edu) {
            $eduText = strtolower(($edu['degree'] ?? '') . ' ' . ($edu['field'] ?? ''));
            $eduHonors = strtolower($edu['honors'] ?? $edu['awards'] ?? '');

            // 1. Degree Level Matching (40% of education score by default)
            $degreeScore = 0;
            $highestDegreeLevel = 0;

            foreach ($degreeLevels as $keyword => $points) {
                if (strpos($eduText, $keyword) !== false) {
                    $highestDegreeLevel = max($highestDegreeLevel, $points);
                }
            }

            // Check job requirement for minimum degree level
            $requiredDegreeLevel = $this->getRequiredDegreeLevel($jobQualification);
            if ($requiredDegreeLevel > 0) {
                if ($highestDegreeLevel >= $requiredDegreeLevel) {
                    $degreeScore = 100;
                } else {
                    $degreeScore = ($highestDegreeLevel / $requiredDegreeLevel) * 100;
                }
            } else {
                $degreeScore = $highestDegreeLevel;
            }

            // 2. Field of Study Matching (40% of education score by default)
            $fieldScore = 0;
            $jobField = $this->detectJobField($jobQualification, $fieldCategories);

            if ($jobField) {
                $fieldKeywords = $fieldCategories[$jobField] ?? [];
                foreach ($fieldKeywords as $keyword) {
                    if (strpos($eduText, $keyword) !== false) {
                        $fieldScore = 100;
                        break;
                    }
                }

                // If no direct match, check for partial matches
                if ($fieldScore == 0) {
                    $eduField = $this->detectJobField($eduText, $fieldCategories);
                    if ($eduField == $jobField) {
                        $fieldScore = 80; // Same category but not exact keyword match
                    }
                }
            } else {
                // If job doesn't specify a field, do keyword matching
                $words = array_filter(
                    preg_split('/\s+/', $jobQualification),
                    function ($word) {
                        return strlen($word) > 3;
                    }
                );

                $matches = 0;
                foreach ($words as $word) {
                    if (strpos($eduText, $word) !== false) {
                        $matches++;
                    }
                }

                $fieldScore = $words ? ($matches / count($words)) * 100 : 50; // Default 50% if no clear field
            }

            // 3. Academic Honors Matching (20% of education score by default)
            $honorsScore = 0;

            // Check for honors in education entry
            foreach ($honorsKeywords as $keyword => $points) {
                if (strpos($eduHonors, $keyword) !== false) {
                    $honorsScore = max($honorsScore, $points);
                }
            }

            // Also check in the main education text for honors mentions
            if ($honorsScore == 0) {
                foreach ($honorsKeywords as $keyword => $points) {
                    if (strpos($eduText, $keyword) !== false) {
                        $honorsScore = max($honorsScore, $points * 0.8); // Slightly lower if not in honors field
                    }
                }
            }

            // Calculate weighted score for this education entry
            $weightedScore = (
                ($degreeScore * $degreeWeight / 100) +
                ($fieldScore * $fieldWeight / 100) +
                ($honorsScore * $honorsWeight / 100)
            );

            $bestMatch = max($bestMatch, $weightedScore);
        }

        return min($bestMatch, 100);
    }

    private function getRequiredDegreeLevel($jobQualification): int
    {
        $degreeLevels = [
            'phd' => 100,
            'doctorate' => 100,
            'master' => 90,
            'masters' => 90,
            'bachelor' => 80,
            'bachelors' => 80,
            'associate' => 70,
            'diploma' => 60,
            'certificate' => 50,
            'high school' => 40,
        ];

        foreach ($degreeLevels as $keyword => $level) {
            if (strpos($jobQualification, $keyword) !== false) {
                return $level;
            }
        }

        return 0; // No specific degree requirement
    }

    private function detectJobField($text, $fieldCategories): ?string
    {
        foreach ($fieldCategories as $field => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($text, $keyword) !== false) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Match experience requirements
     */
    private function matchExperience($yearsExperience, $job): float
    {
        // Simplified implementation - you can copy your existing logic here
        preg_match('/(\d+)\+?\s*(years?|yrs?)/i', $job->description . ' ' . $job->qualification, $matches);
        $requiredYears = isset($matches[1]) ? (int) $matches[1] : 2;

        if ($yearsExperience >= $requiredYears) {
            return 100;
        } elseif ($yearsExperience > 0) {
            return ($yearsExperience / $requiredYears) * 100;
        }

        return 0;
    }

    /**
     * Match work experience relevance
     */
    private function matchWorkExperienceRelevance($workHistory, $job): float
    {
        $jobTitle = strtolower($job->title);
        $jobDescription = strtolower($job->description);
        $jobSkills = strtolower($job->skill);

        $totalRelevance = 0;
        $jobCount = 0;

        foreach ($workHistory as $jobExp) {
            $relevance = 0;
            $jobTitleExp = strtolower($jobExp['job_title'] ?? '');
            $responsibilities = implode(' ', array_map('strtolower', $jobExp['responsibilities'] ?? []));
            $technologies = implode(' ', array_map('strtolower', $jobExp['technologies'] ?? []));

            // Title similarity (30%)
            similar_text($jobTitle, $jobTitleExp, $titleSimilarity);
            $relevance += $titleSimilarity * 0.3;

            // Responsibility keyword matching (40%)
            $jobKeywords = array_filter(
                preg_split('/\s+/', $jobDescription),
                function ($word) {
                    return strlen($word) > 4;
                }
            );

            $keywordMatches = 0;
            foreach ($jobKeywords as $keyword) {
                if (strpos($responsibilities, $keyword) !== false) {
                    $keywordMatches++;
                }
            }

            $keywordScore = $jobKeywords ? ($keywordMatches / count($jobKeywords)) * 100 : 0;
            $relevance += $keywordScore * 0.4;

            // Technology/skills matching (30%)
            $jobSkillArray = array_map('trim', explode(',', $jobSkills));
            $techMatches = 0;

            foreach ($jobSkillArray as $skill) {
                if (strpos($technologies, strtolower($skill)) !== false) {
                    $techMatches++;
                }
            }

            $techScore = $jobSkillArray ? ($techMatches / count($jobSkillArray)) * 100 : 0;
            $relevance += $techScore * 0.3;

            $totalRelevance += min($relevance, 100);
            $jobCount++;
        }

        return $jobCount > 0 ? ($totalRelevance / $jobCount) : 0;
    }

    /**
     * Match general qualifications
     */
    private function matchGeneralQualifications($data, $job): float
    {
        $score = 0;

        // Skills matching (50%)
        $jobSkills = array_map('strtolower', array_map('trim', explode(',', $job->skill ?? '')));

        $resumeSkills = [];

        foreach ($data['skills'] ?? [] as $skillGroup) {

            // Case 1: skills are plain strings
            if (is_string($skillGroup)) {
                $resumeSkills[] = strtolower(trim($skillGroup));
            }

            // Case 2: structured AI output
            if (is_array($skillGroup) && isset($skillGroup['items'])) {
                foreach ($skillGroup['items'] as $skill) {
                    $resumeSkills[] = strtolower(trim($skill));
                }
            }
        }

        $resumeSkills = array_unique($resumeSkills);

        $skillMatches = array_intersect($jobSkills, $resumeSkills);

        $skillScore = count($jobSkills) > 0
            ? (count($skillMatches) / count($jobSkills)) * 100
            : 0;

        $score += $skillScore * 0.5;

        // Certifications (30%)
        $certCount = count($data['certifications'] ?? []);
        $certScore = $certCount >= 5 ? 100 : ($certCount >= 3 ? 75 : ($certCount >= 1 ? 50 : 0));

        $score += $certScore * 0.3;

        // Soft skills (20%)
        $softSkills = $data['soft_skills'] ?? [];

        $softSkillScore = count($softSkills) > 5
            ? 100
            : (count($softSkills) * 20);

        $score += $softSkillScore * 0.2;

        // Location bonus (5%)
        $matchLocationScore = 0;

        $applicantAddress = $data['address'] ?? null;
        $companyAddress = $job->company->location ?? null;

        if ($applicantAddress && $companyAddress) {

            $applicantCoords = $this->geocodeAddress($applicantAddress);
            $companyCoords = $this->geocodeAddress($companyAddress);

            if ($applicantCoords && $companyCoords) {

                $distance = $this->haversineDistance(
                    $applicantCoords['lat'],
                    $applicantCoords['lng'],
                    $companyCoords['lat'],
                    $companyCoords['lng']
                );

                if ($distance <= 5) {
                    $matchLocationScore = 100;
                } elseif ($distance <= 20) {
                    $matchLocationScore = 100 * (1 - ($distance - 5) / 15);
                } elseif ($distance <= 50) {
                    $matchLocationScore = 50 * (1 - ($distance - 20) / 30);
                } else {
                    $matchLocationScore = 0;
                }
            }
        }

        // Apply location weight
        $score = ($score * 0.95) + ($matchLocationScore * 0.05);

        return min($score, 100);
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // distance in km
    }

    private function geocodeAddress($address)
    {
        $address = urlencode($address);
        $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";

        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data)) {
                return [
                    'lat' => (float) $data[0]['lat'],
                    'lng' => (float) $data[0]['lon'],
                ];
            }
        }

        return null;
    }

    /**
     * Get passing threshold
     */
    private function getPassingThreshold($job, $settings): float
    {
        return $settings->minimum_match_percentage ?? 70;
    }
}