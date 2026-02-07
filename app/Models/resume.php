<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Resume extends Model
{
    protected $table = 'resumes';
    protected $fillable = [
        'job_posting_id',
        'applicant_name',
        'applicant_email',
        'years_of_experience',
        'education',
        'skills',
        'certifications',
        'work_history',
        'raw_text',
        'embedding',
        'education_percentage',
        'experience_percentage',
        'skills_percentage',
        'certifications_percentage',
        'match_percentage',
        'status',
        'created_by',
        'updated_by',
    ];

    public function job()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');  // Updated
    }
}
