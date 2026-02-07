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
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function resumes()
    {
        return $this->hasMany(Resume::class, 'job_posting_id');
    }
}
