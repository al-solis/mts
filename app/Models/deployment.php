<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class deployment extends Model
{
    protected $table = 'deployments';

    protected $fillable = [
        'resume_id',
        'salary',
        'start_date',
        'end_date',
        'agency_fee',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    public function resume()
    {
        return $this->belongsTo(resume::class);
    }
}
