<?php

use App\Http\Controllers\TrackedOrderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/tracked-orders');
Route::get('/tracked-orders', [TrackedOrderController::class, 'index'])->name('tracked-orders.index');
Route::get('/tracked-orders/{trackedOrder}', [TrackedOrderController::class, 'show'])->name('tracked-orders.show');
