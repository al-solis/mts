<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Models\JobPosting;
use App\Models\Setting;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\ResumeTextExtractor;
use App\Services\AI\ResumePhotoExtractor;

class ProcessResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $publicPath;
    protected $originalName;
    protected $jobId;
    protected $userId;

    public function __construct($publicPath, $originalName, $jobId, $userId)
    {
        $this->publicPath = $publicPath;
        $this->originalName = $originalName;
        $this->jobId = $jobId;
        $this->userId = $userId;
    }

    public function handle(
        ResumeExtractionService $aiExtractor,
        EmbeddingService $embedder,
        ResumeTextExtractor $textExtractor
    ) {
        Log::info("Processing resume job", [
            'file' => $this->originalName,
            'public_path' => $this->publicPath
        ]);

        try {
            $job = JobPosting::with('company')->findOrFail($this->jobId);
            $settings = Setting::first();

            // Normalize the path for Windows
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $this->publicPath);

            $photoExtractor = new ResumePhotoExtractor();
            $photo = $photoExtractor->extract($fullPath);

            if ($photo) {
                $photoUrl = asset('storage/' . $photo);
                Log::info('Photo extracted successfully', [
                    'file' => $this->originalName,
                    'photo_path' => $photo,
                    'photo_url' => $photoUrl
                ]);
            } else {
                $photoUrl = null;
                Log::warning('No photo found in resume', [
                    'file' => $this->originalName,
                    'file_type' => pathinfo($fullPath, PATHINFO_EXTENSION)
                ]);
            }

            Log::info('Attempting to extract text from file', [
                'full_path' => $fullPath,
                'file_exists' => file_exists($fullPath) ? 'yes' : 'no',
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);

            // Check if file exists before trying to extract
            if (!file_exists($fullPath)) {
                Log::error('Resume file does not exist', [
                    'path' => $fullPath
                ]);
                return;
            }

            // --- Determine file type ---
            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            Log::info('Resume file info', [
                'file' => $this->originalName,
                'extension' => $extension,
                'exists' => file_exists($fullPath) ? 'yes' : 'no',
                'file_size' => file_exists($fullPath) ? filesize($fullPath) : 0
            ]);

            // Extract text from the file
            $text = $textExtractor->extract($fullPath);

            if (empty(trim($text))) {
                Log::warning("Empty resume text", [
                    'file' => $this->originalName,
                    'path' => $fullPath,
                    'file_size' => filesize($fullPath)
                ]);

                // Try to read raw content for debugging
                $rawContent = file_get_contents($fullPath);
                Log::debug('Raw file content preview', [
                    'preview' => substr($rawContent, 0, 200)
                ]);

                // Clean up - delete the file
                $this->cleanupFile();
                return;
            }

            Log::info('Text extracted successfully', [
                'file' => $this->originalName,
                'text_length' => strlen($text)
            ]);

            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            try {
                $data = $aiExtractor->extract($text);
            } catch (\Throwable $e) {
                Log::error("AI extraction failed", [
                    'file' => $this->originalName,
                    'error' => $e->getMessage()
                ]);

                $data = [
                    'name' => 'Unknown Applicant',
                    'email' => null,
                    'years_experience' => 0,
                    'education' => [],
                    'skills' => [],
                    'certifications' => [],
                    'work_history' => []
                ];
            }

            // Use the ResumeMatchingService
            $matchingService = app(\App\Services\AI\ResumeMatchingService::class);
            $matchResult = $matchingService->calculateMatch($data, $job, $settings);

            $passingThreshold = $settings->minimum_match_percentage ?? 70;

            $status = $matchResult['total_percentage'] >= $passingThreshold
                ? 'Passed'
                : 'Failed';

            Resume::create([
                'job_posting_id' => $job->id,
                'applicant_name' => $data['name'] ?? 'Unknown Applicant',
                'email' => $data['email'] ?? null,
                'photo' => $photo ?? null,
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
                'relevance_percentage' => $matchResult['relevance_percentage'],
                'general_percentage' => $matchResult['general_percentage'],
                'match_percentage' => $matchResult['total_percentage'],
                'status' => $status,
                'tag' => 0,
                'created_by' => $this->userId,
                'updated_by' => $this->userId,
            ]);

            Log::info('Resume processed successfully', [
                'file' => $this->originalName,
                'match_percentage' => $matchResult['total_percentage']
            ]);

            // Clean up - delete the file after successful processing
            $this->cleanupFile();

        } catch (\Throwable $e) {
            Log::error("Resume processing failed", [
                'file' => $this->originalName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up - delete the file even if processing failed
            $this->cleanupFile();
        }
    }

    /**
     * Delete the file from public folder
     */
    private function cleanupFile(): void
    {
        try {
            $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $this->publicPath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
                Log::info('Deleted file from public folder', [
                    'path' => $fullPath
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete file', [
                'path' => $this->publicPath,
                'error' => $e->getMessage()
            ]);
        }
    }
}