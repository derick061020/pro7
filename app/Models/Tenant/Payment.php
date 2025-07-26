<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Hyn\Tenancy\Traits\UsesTenantConnection;

class Payment extends Model
{
    use SoftDeletes, UsesTenantConnection;

    protected $table = 'payments';
    
    protected $fillable = [
        'date',
        'amount',
        'payment_method_type_id',
        'payment_destination_id',
        'type',
        'notes',
        'customer',
        'description',
        'reference',
    ];

    protected $casts = [
        'customer' => 'json',
        'date' => 'date',
        'notes' => 'json',
        'amount' => 'decimal:2',
    ];

}
