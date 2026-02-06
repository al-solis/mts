<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class Resume extends Model
{
    protected $table = 'resumes';
    protected $fillable = [
        'job_id',
        'applicant_name',
        'applicant_email',
        'years_of_experience',
        'education',
        'skills',
        'certifications',
        'work_history',
        'raw_text',
        'embedding',
        'created_by',
        'updated_by',
    ];

    public function job()
    {
        return $this->belongsTo(job::class, 'job_id');
    }
}
