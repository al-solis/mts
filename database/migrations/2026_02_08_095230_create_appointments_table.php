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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('company_id');
            // $table->foreign('company_id')->references('id')->on('companies');
            // $table->unsignedBigInteger('job_id');
            // $table->foreign('job_id')->references('id')->on('jobs');
            $table->unsignedBigInteger('resume_id');
            $table->foreign('resume_id')->references('id')->on('resumes');
            $table->integer('meeting_type'); // 1 for in-person, 2 for virtual
            $table->string('interview_round'); // e.g., "First Round", "Second Round"
            $table->date('interview_date');
            $table->dateTime('interview_time');
            $table->text('meeting_link')->nullable(); // For virtual meetings
            $table->text('notes')->nullable();
            $table->integer('status')->default(0); // 0 for scheduled, 1 for completed, 2 for canceled
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
