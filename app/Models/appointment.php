<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class appointment extends Model
{
    protected $table = 'appointments';

    protected $fillable = [
        'resume_id',
        'meeting_type',
        'interview_round',
        'interview_date',
        'interview_time',
        'meeting_link',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'interview_date' => 'date',
    ];


    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resume_id');
    }
}
