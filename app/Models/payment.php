<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
