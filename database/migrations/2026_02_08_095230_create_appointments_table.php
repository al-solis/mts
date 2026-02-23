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
            $table->unsignedBigInteger('resume_id');
            $table->foreign('resume_id')->references('id')->on('resumes');
            $table->integer('meeting_type'); // 1 for in-person, 2 for virtual
            $table->integer('interview_round'); // e.g., 1:"First Round", 2:"Second Round", 3: Third Round", 4: Final Round, 5: Other
            $table->date('interview_date');
            $table->dateTime('interview_time');
            $table->text('meeting_link')->nullable(); // For virtual meetings
            $table->text('notes')->nullable();
            $table->integer('status')->default(0); // 0 for scheduled, 1 for completed, 2 for cancelled
            $table->integer('tag')->default(0); // 0 for no tag, 1 passed, 2 failed
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
