<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\ResumeTextExtractor;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Resume;
use App\Models\JobPosting;
use App\Models\setting;
use App\Models\appointment;
use App\Models\Company;
use App\Jobs\ProcessResumeJob;

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
        $query = Resume::with([
            'job',
            'appointments' => function ($query) {
                $query->latest();
            }
        ])
            ->where('job_posting_id', $jobId)
            ->orderBy('created_at', 'desc');

        $count = $query->count();
        $settings = Setting::first();

        $resumes = $query->get()->map(function ($resume) use ($settings) {
            $job = $resume->job;
            $passingThreshold = $this->getPassingThreshold($job, $settings);

            return [
                'id' => $resume->id,
                'applicant' => $resume->applicant_name,
                'job' => $job->title,
                'photo' => $resume->photo,
                'education' => $resume->education_percentage,
                'experience' => $resume->experience_percentage,
                'general' => $resume->general_percentage ?? 0,
                'match' => $resume->match_percentage,
                'relevance' => $resume->relevance_percentage ?? 0,
                'status' => $resume->status,
                'passing_threshold' => $passingThreshold,
                'tag' => $resume->tag,
                'interview_round' => $resume->appointments->first()?->interview_round ?? null,
                'created_at' => $resume->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => $resumes,
            'count' => $count
        ]);
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'resumes' => 'required|array|max:10'
        ]);

        $jobs = [];

        foreach ($request->file('resumes') as $file) {
            try {
                // Create a unique filename
                $filename = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.]/', '_', $file->getClientOriginalName());

                // Define public path
                $publicPath = public_path('uploads/resumes/' . $filename);

                // Create directory if it doesn't exist
                if (!file_exists(public_path('uploads/resumes'))) {
                    mkdir(public_path('uploads/resumes'), 0777, true);
                }

                // Copy file to public directory
                copy($file->getRealPath(), $publicPath);

                Log::info('File copied to public folder', [
                    'original_name' => $file->getClientOriginalName(),
                    'public_path' => $publicPath,
                    'file_exists' => file_exists($publicPath) ? 'yes' : 'no',
                    'file_size' => filesize($publicPath)
                ]);

                $jobs[] = new ProcessResumeJob(
                    $publicPath,  // Pass the full public path
                    $file->getClientOriginalName(),
                    $request->job_id,
                    Auth::id()
                );

            } catch (\Exception $e) {
                Log::error('Failed to copy file to public folder', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'error' => 'Failed to upload file: ' . $file->getClientOriginalName()
                ], 500);
            }
        }

        Bus::batch($jobs)
            ->name('Resume Processing Batch')
            ->dispatch();

        return response()->json([
            'message' => 'Resumes uploaded and processing in background',
            'count' => count($jobs)
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

    function getIndustrySkills($industry)
    {
        $industrySkills = [

            'Information Technology' => [
                'php',
                'javascript',
                'python',
                'java',
                'sql',
                'html',
                'css',
                'laravel',
                'react',
                'vue',
                'nodejs',
                'mysql',
                'api',
                'git'
            ],

            'Healthcare' => [
                'patient care',
                'medical terminology',
                'ehr',
                'clinical documentation',
                'vital signs',
                'infection control',
                'pharmacology'
            ],

            'Finance' => [
                'accounting',
                'financial analysis',
                'bookkeeping',
                'taxation',
                'audit',
                'excel',
                'quickbooks',
                'budgeting',
                'forecasting'
            ],

            'Manufacturing' => [
                'quality control',
                'production planning',
                'lean manufacturing',
                'supply chain',
                'inventory management',
                'maintenance'
            ],

            'Education' => [
                'curriculum development',
                'lesson planning',
                'classroom management',
                'student assessment',
                'e-learning',
                'research'
            ],

            'Construction' => [
                'project management',
                'autocad',
                'cost estimation',
                'site supervision',
                'blueprint reading',
                'safety compliance'
            ],

            'Retail' => [
                'sales',
                'inventory control',
                'customer service',
                'merchandising',
                'pos systems',
                'cash handling'
            ],

            'BPO' => [
                'customer support',
                'call handling',
                'crm',
                'technical support',
                'email support',
                'data entry'
            ]
        ];

        return $industrySkills[$industry] ?? [];
    }

    public function calculateMatch($data, $job, $settings)
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

    private function matchEducation($resumeEducation, $jobQualification, $settings = null): float
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

    /**
     * Get the required degree level from job qualification
     */
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

    /**
     * Detect the field category from text
     */
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
