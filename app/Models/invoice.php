<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'company_id',
        'description',
        'amount',
        'payment',
        'due_date',
        'payment_terms',
        'billing_cycle',
        'payment_method',
        'status',
        'created_by',
        'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
