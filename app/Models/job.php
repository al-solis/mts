<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\company;

class job extends Model
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
        'created_by',
        'updated_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
