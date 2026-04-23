<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\company;

class JobPosting extends Model
{
    protected $table = 'job_postings';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'qualification',
        'skill',
        'salary_range',
        'status',
        'passing_threshold',
        'threshold_type',
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(company::class, 'company_id');
    }

    public function resumes()
    {
        return $this->hasMany(resume::class, 'job_posting_id');
    }

    public function deployments()
    {
        return $this->hasManyThrough(deployment::class, resume::class, 'job_posting_id', 'resume_id');
    }
}
