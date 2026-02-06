<?php

namespace App\Services\Matching;


class MatchScoringService
{
    protected array $weights = [
        'education' => 0.20,
        'experience_years' => 0.20,
        'work_relevance' => 0.20,
        'skills' => 0.20,
        'certifications' => 0.07,
        'general' => 0.13,
    ];


    public function score(array $job, array $resume): float
    {
        $score = 0;
        $score += $this->educationMatch($job, $resume) * $this->weights['education'];
        $score += $this->yearsMatch($job, $resume) * $this->weights['experience_years'];
        $score += $this->skillsMatch($job, $resume) * $this->weights['skills'];
        $score += $this->certMatch($job, $resume) * $this->weights['certifications'];
        $score += $this->generalMatch($job, $resume) * $this->weights['general'];
        return round($score * 100, 2);
    }


    protected function educationMatch($job, $resume)
    {
        return 1;
    }
    protected function yearsMatch($job, $resume)
    {
        return min(1, $resume['years_experience'] / $job['min_years']);
    }
    protected function skillsMatch($job, $resume)
    {
        return count(array_intersect($job['skills'], $resume['skills'])) / max(1, count($job['skills']));
    }
    protected function certMatch($job, $resume)
    {
        return 0.8;
    }
    protected function generalMatch($job, $resume)
    {
        return 0.9;
    }
}