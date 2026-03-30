<?php

return [
    'shared_token' => env('TRACKER_SHARED_TOKEN', ''),

    'allowed_statuses' => [
        'pending',
        'approved',
        'processing',
        'completed',
        'cancelled',
    ],

    'allowed_payment_statuses' => [
        'unpaid',
        'paid',
        'failed',
        'refunded',
    ],
];
