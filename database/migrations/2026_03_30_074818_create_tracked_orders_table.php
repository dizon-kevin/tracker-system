<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/xxxx_create_tracked_orders_table.php
Schema::create('tracked_orders', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('storix_order_id')->unique(); // ID from Storix
    $table->unsignedBigInteger('storix_user_id');
    $table->string('status')->default('pending'); // pending, approved, completed, cancelled
    $table->decimal('total_price', 10, 2);
    $table->json('items');                         // snapshot of order items
    $table->string('payment_status')->default('unpaid'); // unpaid, paid, failed
    $table->string('xendit_invoice_id')->nullable();
    $table->string('prgc_ref')->nullable();
    $table->timestamp('placed_at')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracked_orders');
    }

    
};
