<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\ResumeTextExtractor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Resume;
use App\Models\JobPosting;
use App\Models\setting;
use Illuminate\Support\Facades\Log;


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
                    'applicant' => $resume->applicant_name,
                    'job' => $job->title,
                    'education' => $resume->education_percentage,
                    'experience' => $resume->experience_percentage,
                    'skills' => $resume->skills_percentage,
                    'certifications' => $resume->certifications_percentage,
                    'match' => $resume->match_percentage,
                    'status' => $resume->status,
                    'passing_threshold' => $passingThreshold,
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
                'resumes.*' => 'file|mimes:pdf,doc,docx,txt|max:51200',
            ]);

            Log::info('Validation passed', $validated);

        } catch (\Illuminate\Validation\ValidationException $e) {
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
                    'raw_text' => $text,
                    'embedding' => json_encode($embedder->embed($text)),
                    'education_percentage' => $matchResult['education_percentage'],
                    'experience_percentage' => $matchResult['experience_percentage'],
                    'skills_percentage' => $matchResult['skills_percentage'],
                    'certifications_percentage' => $matchResult['certifications_percentage'],
                    'match_percentage' => $matchResult['total_percentage'],
                    'status' => $status,
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
                    'match' => round($matchResult['total_percentage'], 2),
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
        // Debug the data structure
        Log::info('AI Extracted Data Structure', [
            'education_type' => gettype($data['education'] ?? 'not set'),
            'education_value' => $data['education'] ?? 'not set',
            'skills_type' => gettype($data['skills'] ?? 'not set'),
            'skills_value' => $data['skills'] ?? 'not set',
            'certifications_type' => gettype($data['certifications'] ?? 'not set'),
            'certifications_value' => $data['certifications'] ?? 'not set',
        ]);

        // Calculate education match
        $educationPercentage = $this->matchEducation($data['education'] ?? [], $job->qualification);

        // Calculate experience match
        $experiencePercentage = $this->matchExperience($data['years_experience'] ?? 0, $job);

        // Calculate skills match
        $skillsPercentage = $this->matchSkills($data['skills'] ?? [], $job->skill);

        // Calculate certifications match
        $certificationsPercentage = $this->matchCertifications($data['certifications'] ?? []);

        // Apply weights from settings
        $total = (
            ($educationPercentage * ($settings->education / 100)) +
            ($experiencePercentage * ($settings->years_of_experience / 100)) +
            ($skillsPercentage * ($settings->skills / 100)) +
            ($certificationsPercentage * ($settings->certifications / 100))
        );

        Log::info('Match Calculation Results', [
            'education' => $educationPercentage,
            'experience' => $experiencePercentage,
            'skills' => $skillsPercentage,
            'certifications' => $certificationsPercentage,
            'total' => $total
        ]);

        return [
            'education_percentage' => $educationPercentage,
            'experience_percentage' => $experiencePercentage,
            'skills_percentage' => $skillsPercentage,
            'certifications_percentage' => $certificationsPercentage,
            'total_percentage' => $total
        ];
    }

    private function matchEducation($resumeEducation, $jobQualification)
    {
        // Ensure $resumeEducation is an array
        if (!is_array($resumeEducation)) {
            $resumeEducation = [];
        }

        // Convert education array to a single lowercase string
        $educationText = '';
        foreach ($resumeEducation as $education) {
            if (is_array($education)) {
                // If education item is an array (like ["institution": "...", "degree": "..."])
                foreach ($education as $key => $value) {
                    if (is_string($value)) {
                        $educationText .= ' ' . strtolower($value);
                    }
                }
            } elseif (is_string($education)) {
                $educationText .= ' ' . strtolower($education);
            }
        }

        $qualificationText = strtolower($jobQualification);

        // If no education text or qualification, return 0
        if (empty(trim($educationText)) || empty(trim($qualificationText))) {
            return 0;
        }

        // Split qualification into words
        $qualificationWords = array_filter(
            preg_split('/\s+/', $qualificationText),
            function ($word) {
                return strlen($word) > 3; // Only consider words longer than 3 characters
            }
        );

        if (empty($qualificationWords)) {
            return 0;
        }

        $matches = 0;
        foreach ($qualificationWords as $word) {
            if (strpos($educationText, $word) !== false) {
                $matches++;
            }
        }

        return ($matches / count($qualificationWords)) * 100;
    }

    private function matchExperience($yearsExperience, $job)
    {
        // Extract years from job description if possible
        preg_match('/(\d+)\+?\s*(years?|yrs?)/i', $job->description . ' ' . $job->qualification, $matches);

        $requiredYears = isset($matches[1]) ? (int) $matches[1] : 2; // Default to 2 years

        if ($yearsExperience >= $requiredYears) {
            return 100;
        } elseif ($yearsExperience > 0) {
            return ($yearsExperience / $requiredYears) * 100;
        }

        return 0;
    }

    private function matchSkills($resumeSkills, $jobSkills)
    {
        // Ensure both are arrays
        if (!is_array($resumeSkills)) {
            $resumeSkills = [];
        }

        $jobSkillArray = array_map('strtolower', array_filter(
            explode(',', $jobSkills),
            function ($skill) {
                return !empty(trim($skill));
            }
        ));

        // Convert resume skills to lowercase strings
        $resumeSkillArray = [];
        foreach ($resumeSkills as $skill) {
            if (is_string($skill)) {
                $resumeSkillArray[] = strtolower(trim($skill));
            } elseif (is_array($skill)) {
                foreach ($skill as $key => $value) {
                    if (is_string($value)) {
                        $resumeSkillArray[] = strtolower(trim($value));
                    }
                }
            }
        }

        if (empty($jobSkillArray)) {
            return 0;
        }

        $matches = array_intersect($jobSkillArray, array_unique($resumeSkillArray));

        return (count($matches) / count($jobSkillArray)) * 100;
    }

    private function matchCertifications($resumeCertifications)
    {
        // Ensure it's an array
        if (!is_array($resumeCertifications)) {
            $resumeCertifications = [];
        }

        // Count actual certification strings
        $certCount = 0;
        foreach ($resumeCertifications as $cert) {
            if (is_string($cert) && !empty(trim($cert))) {
                $certCount++;
            } elseif (is_array($cert)) {
                foreach ($cert as $key => $value) {
                    if (is_string($value) && !empty(trim($value))) {
                        $certCount++;
                    }
                }
            }
        }

        if ($certCount >= 5)
            return 100;
        if ($certCount >= 3)
            return 75;
        if ($certCount >= 1)
            return 50;

        return 0;
    }

    private function getPassingThreshold($job, $settings)
    {
        if ($job->threshold_type === 'custom') {
            return $job->passing_threshold;
        }

        return $settings->{$job->threshold_type} ?? 70;
    }
}
