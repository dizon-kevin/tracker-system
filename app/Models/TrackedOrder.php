<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackedOrder extends Model
{
    protected $fillable = [
        'storix_order_id',
        'storix_user_id',
        'status',
        'total_price',
        'items',
        'payment_status',
        'payment_method',
        'payment_amount',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'xendit_payment_method',
        'xendit_reference_id',
        'prgc_ref',
        'pickup_address',
        'delivery_address',
        'placed_at',
        'approved_at',
        'completed_at',
        'payment_paid_at',
        'payment_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total_price' => 'decimal:2',
            'payment_amount' => 'decimal:2',
            'pickup_address' => 'array',
            'delivery_address' => 'array',
            'placed_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'payment_paid_at' => 'datetime',
            'payment_expires_at' => 'datetime',
        ];
    }
}
