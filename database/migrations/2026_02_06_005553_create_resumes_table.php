<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_posting_id');
            $table->foreign('job_posting_id')->references('id')->on('job_postings');
            $table->string('applicant_name');
            $table->string('email')->nullable();
            $table->integer('years_experience')->default(0);
            $table->json('education')->nullable();
            $table->json('work_history')->nullable();
            $table->json('general_qualifications')->nullable();
            $table->json('skills')->nullable();
            $table->json('certifications')->nullable();
            $table->json('soft_skills')->nullable();
            $table->text('raw_text');
            $table->json('embedding')->nullable();

            $table->decimal('education_percentage', 5, 2)->default(0);
            $table->decimal('experience_percentage', 5, 2)->default(0);
            $table->decimal('skills_percentage', 5, 2)->default(0);
            $table->decimal('certifications_percentage', 5, 2)->default(0);
            $table->decimal('soft_skills_percentage', 5, 2)->default(0);
            $table->decimal('relevance_percentage', 5, 2)->default(0);
            $table->decimal('general_percentage', 5, 2)->default(0);
            $table->decimal('match_percentage', 5, 2)->default(0);
            $table->enum('status', ['Passed', 'Failed'])->default('Failed');
            $table->integer('tag')->default(0); // 0 = pending, 1 = scheduled, 2 = passed, 3 = hold, 4 = rejected
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
