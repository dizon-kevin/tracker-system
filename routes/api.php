<?php

use App\Http\Controllers\Api\TrackedOrderSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware('tracker.token')->group(function (): void {
    Route::post('/orders/sync', [TrackedOrderSyncController::class, 'sync']);
    Route::post('/orders/{storix_order_id}/status', [TrackedOrderSyncController::class, 'updateStatus']);
});
