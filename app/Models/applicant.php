<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    protected $table = 'applicants';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'created_by',
        'updated_by',
    ];
}
