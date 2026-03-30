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
        'xendit_invoice_id',
        'prgc_ref',
        'placed_at',
        'approved_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'total_price' => 'decimal:2',
            'placed_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
