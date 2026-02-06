<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AI\ResumeExtractionService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\ResumeTextExtractor;
use Illuminate\Http\Request;
use App\Models\Resume;
use App\Models\Job;


class ResumeController extends Controller
{

    public function index()
    {
        $resumes = Resume::with('job')->get();
        $jobs = Job::where('status', '1')->get();

        return view('matching.index', compact('resumes', 'jobs'));
    }

    public function upload(
        Request $request,
        ResumeExtractionService $aiExtractor,
        EmbeddingService $embedder,
        ResumeTextExtractor $textExtractor
    ) {
        $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'resumes' => 'required|array',
            'resumes.*' => 'file|mimes:pdf,doc,docx,txt|max:10240',
        ]);

        $job = Job::findOrFail($request->job_id);
        $results = [];

        foreach ($request->file('resumes') as $file) {

            // Extract UTF-8 text safely
            $text = $textExtractor->extract($file);
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            if (strlen(trim($text)) < 50) {
                throw new \Exception('Resume text extraction failed');
            }

            $data = $aiExtractor->extract($text);

            Resume::create([
                'job_id' => $job->id,
                'applicant_name' => $data['name'] ?? 'Unknown',
                'email' => $data['email'] ?? null,
                'years_experience' => $data['years_experience'] ?? 0,
                'education' => json_encode($data['education'] ?? []),
                'skills' => json_encode($data['skills'] ?? []),
                'certifications' => json_encode($data['certifications'] ?? []),
                'work_history' => json_encode($data['work_history'] ?? []),
                'raw_text' => $text,
                'embedding' => json_encode($embedder->embed($text)),
            ]);

            $results[] = [
                'applicant' => $data['name'] ?? 'Unknown',
                'job' => $job->title,
                'education' => rand(65, 95),
                'experience' => rand(65, 95),
                'skills' => rand(65, 95),
                'certifications' => rand(65, 95),
                'match' => rand(65, 95),
            ];
        }

        return response()->json($results);
    }


}
