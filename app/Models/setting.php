<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'education',
        'years_of_experience',
        'work_experience_relevance',
        'skills',
        'certifications',
        'general',
        'minimum_match_percentage',
        'strict',
        'moderate',
        'flexible',
        'lenient',
        'created_by',
        'updated_by',
    ];
}
