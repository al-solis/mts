<?php

namespace App\Jobs;

use App\Models\resume as Resume;
use App\Models\JobPosting;
use App\Models\GeocodeCache;
use App\Services\AI\ResumeExtractionService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\setting as Setting;
use App\Http\Controllers\ResumeController;

class ProcessResume implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $filePath;
    public $jobId;

    public function __construct(string $filePath, int $jobId)
    {
        $this->filePath = $filePath;
        $this->jobId = $jobId;
    }
    public function handle(ResumeExtractionService $extractor)
    {
        try {
            $batchId = $this->batch()?->id;

            $fullPath = storage_path('app/' . $this->filePath);

            if (!file_exists($fullPath)) {
                Log::error("Resume file not found: {$fullPath}");
                return;
            }

            // Read and pre-trim the resume
            $text = $this->preTrimResume(file_get_contents($fullPath));
            $data = $extractor->extract($text);

            if (!$data || !isset($data['name'])) {
                Log::warning("Resume extraction failed for file: {$this->filePath}");
                return;
            }

            $job = JobPosting::with('company')->find($this->jobId);
            if (!$job)
                return;

            $controller = app(ResumeController::class);
            $settings = Setting::first();
            $matchData = $controller->calculateMatch($data, $job, $settings);

            $resume = Resume::create([
                'applicant_name' => $data['name'] ?? 'N/A',
                'email' => $data['email'] ?? null,
                'job_posting_id' => $this->jobId,
                'education_percentage' => $matchData['education_percentage'] ?? 0,
                'experience_percentage' => $matchData['experience_percentage'] ?? 0,
                'general_percentage' => $matchData['general_percentage'] ?? 0,
                'relevance_percentage' => $matchData['relevance_percentage'] ?? 0,
                'match_percentage' => $matchData['total_percentage'] ?? 0,
                'status' => 1,
                'tag' => 1,
                // store the file path, not the UploadedFile
                'resume_file' => $this->filePath,
            ]);

            Log::info("Resume processed: {$resume->applicant_name}");
        } catch (\Throwable $e) {
            Log::error("ProcessResume failed: {$e->getMessage()}", ['trace' => $e->getTraceAsString()]);
        }
    }

    private function preTrimResume(string $text): string
    {
        return mb_substr(preg_replace('/\s+/', ' ', $text), 0, 20000);
    }

    public function getCoordinates(string $address): ?array
    {
        $cached = GeocodeCache::firstWhere('address', $address);
        if ($cached)
            return ['lat' => $cached->lat, 'lng' => $cached->lng];

        $encoded = urlencode($address);
        $url = "https://nominatim.openstreetmap.org/search?q={$encoded}&format=json&limit=1";

        $response = @file_get_contents($url);
        if ($response) {
            $data = json_decode($response, true);
            if (!empty($data)) {
                $lat = (float) $data[0]['lat'];
                $lng = (float) $data[0]['lon'];
                GeocodeCache::create(['address' => $address, 'lat' => $lat, 'lng' => $lng]);
                return ['lat' => $lat, 'lng' => $lng];
            }
        }
        return null;
    }
}