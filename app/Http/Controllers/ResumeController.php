<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\ResumeTextExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Resume;
use App\Models\JobPosting;
use App\Models\setting;
use App\Models\appointment;


class ResumeController extends Controller
{

    public function index()
    {
        $resumes = Resume::with('job')->get();
        $jobs = JobPosting::where('status', '1')->with('company')->get();

        return view('matching.index', compact('resumes', 'jobs'));
    }

    public function getByJob($jobId)
    {
        $resumes = Resume::with('job')
            ->where('job_posting_id', $jobId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($resume) {
                $job = $resume->job;
                $settings = Setting::first();
                $passingThreshold = $this->getPassingThreshold($job, $settings);

                return [
                    'id' => $resume->id,
                    'applicant' => $resume->applicant_name,
                    'job' => $job->title,
                    'education' => $resume->education_percentage,
                    'experience' => $resume->experience_percentage,
                    'general' => $resume->general_percentage ?? 0,
                    'match' => $resume->match_percentage,
                    'relevance' => $resume->relevance_percentage ?? 0,
                    'status' => $resume->status,
                    'passing_threshold' => $passingThreshold,
                    'tag' => $resume->tag,
                    'created_at' => $resume->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => $resumes,
            'count' => $resumes->count(),
        ]);
    }

    public function upload(
        Request $request,
        ResumeExtractionService $aiExtractor,
        EmbeddingService $embedder,
        ResumeTextExtractor $textExtractor
    ) {
        // Debug the incoming request
        Log::info('Upload request received', [
            'job_id' => $request->job_id,
            'resumes_count' => count($request->file('resumes') ?? []),
            'resumes' => array_map(function ($file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime' => $file->getMimeType()
                ];
            }, $request->file('resumes') ?? [])
        ]);

        try {
            $validated = $request->validate([
                'job_id' => 'required|exists:job_postings,id',
                'resumes' => 'required|array|max:10',
                // 'resumes.*' => 'file|mimes:pdf,doc,docx,txt|max:51200',
            ]);

            Log::info('Validation passed', $validated);

        } catch (ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            // Return validation errors
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $job = JobPosting::with('company')->findOrFail($request->job_id);
        $settings = Setting::first();
        $results = [];
        $failedFiles = [];

        foreach ($request->file('resumes') as $file) {
            $fileName = $file->getClientOriginalName();

            Log::info('Processing file start', ['file' => $fileName]);

            try {
                // Extract text
                $text = $textExtractor->extract($file);

                // Check if extraction was successful
                if (empty(trim($text))) {
                    Log::warning('Text extraction returned empty', ['file' => $fileName]);
                    $failedFiles[] = [
                        'file' => $fileName,
                        'reason' => 'Text extraction failed - file may be empty or corrupted'
                    ];
                    continue;
                }

                // Convert to UTF-8
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
                Log::info('Text extracted successfully', [
                    'file' => $fileName,
                    'text_length' => strlen($text),
                    'preview' => substr($text, 0, 200) . '...'
                ]);

                // Extract structured data
                $data = [];
                try {
                    $data = $aiExtractor->extract($text);
                    Log::info('AI extraction successful', [
                        'file' => $fileName,
                        'data_keys' => array_keys($data)
                    ]);
                } catch (\Throwable $e) {
                    Log::error('AI extraction failed', [
                        'file' => $fileName,
                        'error' => $e->getMessage()
                    ]);

                    // Create basic data structure from text
                    $data = $this->extractBasicInfo($text);
                }

                // Calculate match percentage
                $matchResult = $this->calculateMatch($data, $job, $settings);

                // Determine pass/fail
                $passingThreshold = $this->getPassingThreshold($job, $settings);
                $status = $matchResult['total_percentage'] >= $passingThreshold ? 'Passed' : 'Failed';

                // Save resume
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
                    'embedding' => json_encode($embedder->embed($text)),
                    'education_percentage' => $matchResult['education_percentage'],
                    'experience_percentage' => $matchResult['experience_percentage'],
                    'skills_percentage' => $matchResult['skills_percentage'] ?? 0,
                    'certifications_percentage' => $matchResult['certifications_percentage'] ?? 0,
                    'relevance_percentage' => $matchResult['relevance_percentage'] ?? 0,
                    'general_percentage' => $matchResult['general_percentage'] ?? 0,
                    'match_percentage' => $matchResult['total_percentage'],
                    'status' => $status,
                    'tag' => 0, // pending
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);

                $results[] = [
                    'applicant' => $data['name'] ?? 'Unknown Applicant',
                    'job' => $job->title,
                    'education' => round($matchResult['education_percentage'], 2),
                    'experience' => round($matchResult['experience_percentage'], 2),
                    'skills' => round($matchResult['skills_percentage'], 2),
                    'certifications' => round($matchResult['certifications_percentage'], 2),
                    'relevance' => round($matchResult['relevance_percentage'] ?? 0, 2),
                    'general_percentage' => round($matchResult['general_percentage'], 2),
                    'match' => round($matchResult['total_percentage'], 2),
                    'soft_skills' => round($matchResult['soft_skills_percentage'], 2),
                    'status' => $status,
                    'passing_threshold' => $passingThreshold,
                ];

                Log::info('File processing completed', [
                    'file' => $fileName,
                    'applicant' => $data['name'] ?? 'Unknown',
                    'match_percentage' => $matchResult['total_percentage']
                ]);

            } catch (\Throwable $e) {
                Log::error('File processing failed', [
                    'file' => $fileName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $failedFiles[] = [
                    'file' => $fileName,
                    'reason' => 'Processing error: ' . $e->getMessage()
                ];
            }
        }

        // Return results with failed files info
        return response()->json([
            'success' => $results,
            'failed' => $failedFiles,
            'summary' => [
                'total' => count($request->file('resumes')),
                'processed' => count($results),
                'failed' => count($failedFiles)
            ]
        ]);
    }

    // Fallback method for basic info extraction
    private function extractBasicInfo(string $text): array
    {
        $data = [
            'name' => 'Unknown Applicant',
            'email' => null,
            'years_experience' => 0,
            'education' => [],
            'skills' => [],
            'certifications' => [],
            'work_history' => []
        ];

        // Try to extract email
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emailMatches)) {
            $data['email'] = $emailMatches[0];
        }

        // Try to extract name (simple pattern)
        $lines = explode("\n", $text);
        if (!empty($lines[0]) && strlen(trim($lines[0])) < 100) {
            $data['name'] = trim($lines[0]);
        }

        // Extract skills (simple keyword matching)
        $skillKeywords = ['php', 'javascript', 'python', 'java', 'sql', 'html', 'css', 'laravel', 'react', 'vue'];
        foreach ($skillKeywords as $skill) {
            if (stripos($text, $skill) !== false) {
                $data['skills'][] = ucfirst($skill);
            }
        }

        return $data;
    }

    private function calculateMatch($data, $job, $settings)
    {
        // Calculate each component with their respective weights
        $educationScore = $this->matchEducation($data['education'] ?? [], $job->qualification);
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

        Log::info('Match Calculation Results', [
            'education' => $educationScore,
            'experience' => $experienceScore,
            'relevance' => $relevanceScore,
            'general' => $generalScore,
            'total' => $total
        ]);

        return [
            'education_percentage' => $educationScore,
            'experience_percentage' => $experienceScore,
            'relevance_percentage' => $relevanceScore,
            'general_percentage' => $generalScore,
            'total_percentage' => $total
        ];
    }

    private function matchEducation($resumeEducation, $jobQualification): float
    {
        // Parse job qualification for education requirements
        $jobQualification = strtolower($jobQualification);

        // Check for degree requirements
        $degreeKeywords = [
            'phd' => 100,
            'doctorate' => 100,
            'master' => 80,
            'masters' => 80,
            'bachelor' => 60,
            'bachelors' => 60,
            'bs' => 60,
            'ba' => 60,
            'associate' => 40,
            'diploma' => 30,
            'high school' => 20,
            'secondary' => 20,
            'primary' => 10,
            'elementary' => 10,
            'none' => 0,
        ];

        // Check for field requirements
        $fieldKeywords = [
            'computer science' => ['cs', 'computer', 'software', 'programming'],
            'information technology' => ['it', 'information', 'technology'],
            'engineering' => ['engineering', 'engineer'],
            'business' => ['business', 'administration', 'management'],
            'science' => ['science', 'scientific'],
        ];

        $bestMatch = 0;

        foreach ($resumeEducation as $edu) {
            $eduText = strtolower(($edu['degree'] ?? '') . ' ' . ($edu['field'] ?? ''));
            $score = 0;

            // Degree level matching (40% weight)
            foreach ($degreeKeywords as $keyword => $points) {
                if (strpos($eduText, $keyword) !== false) {
                    $score += $points * 0.4;
                    break;
                }
            }

            // Field of study matching (60% weight)
            foreach ($fieldKeywords as $mainField => $synonyms) {
                if (strpos($jobQualification, $mainField) !== false) {
                    // Job requires this field
                    foreach ($synonyms as $synonym) {
                        if (strpos($eduText, $synonym) !== false) {
                            $score += 100 * 0.6;
                            break 2;
                        }
                    }
                }
            }

            // Keyword matching in qualification text
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

            $keywordScore = $words ? ($matches / count($words)) * 100 : 0;
            $score = max($score, $keywordScore * 0.3); // 30% weight for general keyword matching

            $bestMatch = max($bestMatch, $score);
        }

        return min($bestMatch, 100);
    }

    private function matchExperience($yearsExperience, $job): float
    {
        // Extract years from job description
        preg_match('/(\d+)\+?\s*(years?|yrs?)/i', $job->description . ' ' . $job->qualification, $matches);

        $requiredYears = isset($matches[1]) ? (int) $matches[1] : 2;

        if ($yearsExperience >= $requiredYears) {
            return 100;
        } elseif ($yearsExperience > 0) {
            return ($yearsExperience / $requiredYears) * 100;
        }

        return 0;
    }

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

    private function matchGeneralQualifications($data, $job): float
    {
        $score = 0;

        // Skills matching (50% weight)
        $jobSkills = array_map('strtolower', array_map('trim', explode(',', $job->skill)));
        $resumeSkills = [];

        foreach ($data['skills'] ?? [] as $skillGroup) {
            foreach ($skillGroup['items'] ?? [] as $skill) {
                $resumeSkills[] = strtolower(trim($skill));
            }
        }

        $skillMatches = array_intersect($jobSkills, array_unique($resumeSkills));
        $skillScore = $jobSkills ? (count($skillMatches) / count($jobSkills)) * 100 : 0;
        $score += $skillScore * 0.5;

        // Certifications (30% weight)
        $certCount = count($data['certifications'] ?? []);
        $certScore = 0;

        if ($certCount >= 5)
            $certScore = 100;
        elseif ($certCount >= 3)
            $certScore = 75;
        elseif ($certCount >= 1)
            $certScore = 50;

        $score += $certScore * 0.3;

        // Soft skills (20% weight)
        $softSkills = $data['soft_skills'] ?? [];
        $softSkillScore = count($softSkills) > 5 ? 100 : (count($softSkills) * 20);
        $score += $softSkillScore * 0.2;

        return min($score, 100);
    }

    private function getPassingThreshold($job, $settings)
    {
        // if ($job->threshold_type === 'custom') {
        //     return $job->passing_threshold;
        // }

        return $settings->minimum_match_percentage ?? 70;
    }

    public function getApplicantsByJob($jobId)
    {
        $applicants = Resume::where('job_posting_id', $jobId)
            ->where(function ($query) {
                $query->whereDoesntHave('appointments')
                    ->orWhereHas('appointments', function ($q) {
                        $q->where('status', '!=', 0);
                    });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($applicants);
    }

    public function markAsPassed($id)
    {
        $resume = Resume::findOrFail($id);
        $resume->update(['tag' => 2]); // 2 = Passed

        return response()->json(['success' => true, 'message' => 'Resume marked as passed']);
    }
}
