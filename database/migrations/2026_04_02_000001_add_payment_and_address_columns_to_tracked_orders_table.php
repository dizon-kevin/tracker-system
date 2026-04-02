<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tracked_orders', function (Blueprint $table): void {
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->decimal('payment_amount', 12, 2)->default(0)->after('payment_method');
            $table->string('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->string('xendit_payment_method')->nullable()->after('xendit_invoice_url');
            $table->string('xendit_reference_id')->nullable()->after('xendit_payment_method');
            $table->timestamp('payment_paid_at')->nullable()->after('completed_at');
            $table->timestamp('payment_expires_at')->nullable()->after('payment_paid_at');
            $table->json('pickup_address')->nullable()->after('payment_expires_at');
            $table->json('delivery_address')->nullable()->after('pickup_address');
        });
    }

    public function down(): void
    {
        Schema::table('tracked_orders', function (Blueprint $table): void {
            $table->dropColumn([
                'payment_method',
                'payment_amount',
                'xendit_invoice_url',
                'xendit_payment_method',
                'xendit_reference_id',
                'payment_paid_at',
                'payment_expires_at',
                'pickup_address',
                'delivery_address',
            ]);
        });
    }
};
