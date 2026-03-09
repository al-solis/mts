<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CleanupResumeFiles extends Command
{
    protected $signature = 'resumes:cleanup {hours=24 : Files older than this many hours will be deleted}';
    protected $description = 'Clean up old resume files from public uploads directory';

    public function handle()
    {
        $hours = $this->argument('hours');
        $directory = public_path('uploads/resumes');

        if (!is_dir($directory)) {
            $this->info('Uploads directory does not exist.');
            return 0;
        }

        $files = File::files($directory);
        $cutoff = now()->subHours($hours);
        $deleted = 0;

        foreach ($files as $file) {
            if (File::lastModified($file) < $cutoff->timestamp) {
                File::delete($file);
                $deleted++;
                $this->line("Deleted: " . basename($file));
            }
        }

        // Remove empty subdirectories
        $directories = File::directories($directory);
        foreach ($directories as $dir) {
            if (count(File::files($dir)) === 0 && count(File::directories($dir)) === 0) {
                File::deleteDirectory($dir);
                $this->line("Removed empty directory: " . basename($dir));
            }
        }

        $this->info("Deleted {$deleted} old resume files.");
        Log::info("Resume cleanup completed", ['deleted' => $deleted, 'hours' => $hours]);

        return 0;
    }
}