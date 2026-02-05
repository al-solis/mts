<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('education')->default(20);
            $table->decimal('years_of_experience')->default(20);
            $table->decimal('work_experience_relevance')->default(20);
            $table->decimal('skills')->default(20);
            $table->decimal('certifications')->default(10);
            $table->decimal('general')->default(10);
            $table->decimal('minimum_match_percentage')->default(70);

            $table->decimal('strict')->default(85);
            $table->decimal('moderate')->default(70);
            $table->decimal('flexible')->default(60);
            $table->decimal('lenient')->default(50);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'education' => 20,
            'years_of_experience' => 20,
            'work_experience_relevance' => 20,
            'skills' => 20,
            'certifications' => 10,
            'general' => 10,
            'minimum_match_percentage' => 70,
            'strict' => 85,
            'moderate' => 70,
            'flexible' => 60,
            'lenient' => 50,
            'created_by' => null,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
