<?php

namespace App\Services;

use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\AIEmbeddingService;

class ApplicantMatcher
{
    protected AIEmbeddingService $aiService;

    public function __construct(AIEmbeddingService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Calculate full match breakdown and total percentage
     */
    public function calculateMatch(JobPosting $job, Resume $resume): array
    {
        // 1️⃣ Education Match (20%)
        $educationScore = $this->educationScore($job->required_education, $resume->education_level, 20);

        // 2️⃣ Years of Experience (20%)
        $experienceScore = $this->experienceScore($job->required_experience_years, $resume->years_experience, 20);

        // 3️⃣ Work Experience Relevance (20%) - AI Semantic Match
        $relevanceScore = $this->workRelevanceScore($job->description, $resume, 20);

        // 4️⃣ Skills Match (20%)
        $skillsScore = $this->skillsScore($job->required_skills, $resume->skills, 20);

        // 5️⃣ Related Certifications (7%)
        $certificationsScore = $this->certificationsScore($job->required_certifications ?? [], $resume->certifications ?? [], 7);

        // 6️⃣ General Qualifications (13%)
        $qualificationsScore = $this->generalQualificationsScore($job->general_qualifications, $resume->general_qualifications, 13);

        // Total match percentage
        $total = round(
            $educationScore +
            $experienceScore +
            $relevanceScore +
            $skillsScore +
            $certificationsScore +
            $qualificationsScore,
            2
        );

        return [
            'education' => $educationScore,
            'experience' => $experienceScore,
            'relevance' => $relevanceScore,
            'skills' => $skillsScore,
            'certifications' => $certificationsScore,
            'general_qualifications' => $qualificationsScore,
            'total_match_percentage' => $total,
        ];
    }

    private function educationScore(string $jobEdu, string $resumeEdu, float $weight): float
    {
        $levels = [
            'highschool' => 1,
            'associate' => 2,
            'bachelor' => 3,
            'master' => 4,
            'phd' => 5
        ];

        $jobLevel = $levels[strtolower($jobEdu)] ?? 1;
        $resumeLevel = $levels[strtolower($resumeEdu)] ?? 1;

        return min(($resumeLevel / $jobLevel), 1) * $weight;
    }

    private function experienceScore(int $requiredYears, int $resumeYears, float $weight): float
    {
        return min(($resumeYears / $requiredYears), 1) * $weight;
    }

    private function workRelevanceScore(string $jobDesc, Resume $resume, float $weight): float
    {
        // generate embeddings if not exist
        if (!$resume->ai_embedding) {
            $resumeEmbedding = $this->aiService->generateEmbedding($resume->parsed_text);
            $resume->ai_embedding = json_encode($resumeEmbedding);
            $resume->save();
        } else {
            $resumeEmbedding = json_decode($resume->ai_embedding, true);
        }

        // Job embedding can also be cached
        $jobEmbedding = $this->aiService->generateEmbedding($jobDesc);

        $similarity = $this->aiService->cosineSimilarity($jobEmbedding, $resumeEmbedding);

        return $similarity * $weight;
    }

    private function skillsScore(array $jobSkills, array $resumeSkills, float $weight): float
    {
        $matched = array_intersect($jobSkills, $resumeSkills);
        $ratio = count($matched) / max(count($jobSkills), 1);
        return $ratio * $weight;
    }

    private function certificationsScore(array $jobCerts, array $resumeCerts, float $weight): float
    {
        $matched = array_intersect($jobCerts, $resumeCerts);
        $ratio = count($matched) / max(count($jobCerts), 1);
        return $ratio * $weight;
    }

    private function generalQualificationsScore(string $jobQuals, string $resumeQuals, float $weight): float
    {
        similar_text(strtolower($jobQuals), strtolower($resumeQuals), $percent);
        return ($percent / 100) * $weight;
    }
}
