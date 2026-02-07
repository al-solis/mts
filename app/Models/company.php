<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'name',
        'industry',
        'contact_person',
        'contact_email',
        'location',
        'status',
        'created_by',
        'updated_by',
    ];

    public function jobs()
    {
        return $this->hasMany(JobPosting::class, 'company_id');
    }
}
