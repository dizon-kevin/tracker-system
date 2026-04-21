<?php

namespace Tests\Feature;

use App\Models\TrackedOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackedOrderSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tracker.shared_token', 'test-shared-token');
    }

    public function test_it_creates_a_tracked_order_from_storix(): void
    {
        $response = $this
            ->withToken('test-shared-token')
            ->postJson('/api/orders/sync', [
                'storix_order_id' => 1001,
                'storix_user_id' => 55,
                'status' => 'pending',
                'total_price' => 1499.50,
                'items' => [
                    [
                        'sku' => 'ITEM-001',
                        'name' => 'Sample Item',
                        'quantity' => 2,
                        'price' => 749.75,
                    ],
                ],
                'payment_status' => 'unpaid',
                'placed_at' => now()->toIso8601String(),
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.storix_order_id', 1001)
            ->assertJsonPath('data.payment_status', 'unpaid');

        $this->assertDatabaseHas('tracked_orders', [
            'storix_order_id' => 1001,
            'storix_user_id' => 55,
            'status' => 'pending',
        ]);
    }

    public function test_it_updates_an_existing_tracked_order_status(): void
    {
        $trackedOrder = TrackedOrder::query()->create([
            'storix_order_id' => 1001,
            'storix_user_id' => 55,
            'status' => 'pending',
            'total_price' => 1499.50,
            'items' => [
                [
                    'sku' => 'ITEM-001',
                    'quantity' => 2,
                ],
            ],
            'payment_status' => 'unpaid',
        ]);

        $response = $this
            ->withToken('test-shared-token')
            ->postJson("/api/orders/{$trackedOrder->storix_order_id}/status", [
                'status' => 'approved',
                'payment_status' => 'paid',
                'approved_at' => now()->toIso8601String(),
                'xendit_invoice_id' => 'inv-test-001',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('tracked_orders', [
            'storix_order_id' => 1001,
            'status' => 'approved',
            'payment_status' => 'paid',
            'xendit_invoice_id' => 'inv-test-001',
        ]);
    }

    public function test_it_auto_approves_paid_xendit_orders_without_manual_approval(): void
    {
        $trackedOrder = TrackedOrder::query()->create([
            'storix_order_id' => 1002,
            'storix_user_id' => 56,
            'status' => 'pending',
            'total_price' => 999.00,
            'items' => [
                [
                    'sku' => 'ITEM-002',
                    'quantity' => 1,
                ],
            ],
            'payment_status' => 'pending',
            'payment_method' => 'gcash',
            'xendit_invoice_id' => 'inv-paid-001',
        ]);

        $response = $this
            ->withToken('test-shared-token')
            ->postJson("/api/orders/{$trackedOrder->storix_order_id}/status", [
                'status' => 'pending',
                'payment_status' => 'paid',
                'payment_method' => 'gcash',
                'xendit_invoice_id' => 'inv-paid-001',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('tracked_orders', [
            'storix_order_id' => 1002,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $this->assertNotNull($trackedOrder->fresh()->approved_at);
        $this->assertNotNull($trackedOrder->fresh()->payment_paid_at);
    }

    public function test_it_prevents_xendit_orders_from_being_manually_approved_before_payment_is_paid(): void
    {
        $trackedOrder = TrackedOrder::query()->create([
            'storix_order_id' => 1003,
            'storix_user_id' => 57,
            'status' => 'pending',
            'total_price' => 499.00,
            'items' => [
                [
                    'sku' => 'ITEM-003',
                    'quantity' => 1,
                ],
            ],
            'payment_status' => 'pending',
            'payment_method' => 'gcash',
            'xendit_invoice_id' => 'inv-pending-001',
        ]);

        $response = $this
            ->withToken('test-shared-token')
            ->postJson("/api/orders/{$trackedOrder->storix_order_id}/status", [
                'status' => 'approved',
                'payment_status' => 'expired',
                'payment_method' => 'gcash',
                'xendit_invoice_id' => 'inv-pending-001',
                'approved_at' => now()->toIso8601String(),
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.payment_status', 'expired');

        $this->assertDatabaseHas('tracked_orders', [
            'storix_order_id' => 1003,
            'status' => 'pending',
            'payment_status' => 'expired',
        ]);

        $this->assertNull($trackedOrder->fresh()->approved_at);
    }

    public function test_it_does_not_downgrade_paid_xendit_orders_with_stale_pending_updates(): void
    {
        $trackedOrder = TrackedOrder::query()->create([
            'storix_order_id' => 1004,
            'storix_user_id' => 58,
            'status' => 'approved',
            'total_price' => 799.00,
            'items' => [
                [
                    'sku' => 'ITEM-004',
                    'quantity' => 1,
                ],
            ],
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'xendit_invoice_id' => 'inv-paid-002',
            'approved_at' => now(),
            'payment_paid_at' => now(),
        ]);

        $response = $this
            ->withToken('test-shared-token')
            ->postJson("/api/orders/{$trackedOrder->storix_order_id}/status", [
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'gcash',
                'xendit_invoice_id' => 'inv-paid-002',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('data.payment_status', 'paid');

        $this->assertDatabaseHas('tracked_orders', [
            'storix_order_id' => 1004,
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);
    }

    public function test_it_rejects_requests_without_the_shared_token(): void
    {
        $response = $this->postJson('/api/orders/sync', [
            'storix_order_id' => 1001,
            'storix_user_id' => 55,
            'total_price' => 1499.50,
            'items' => [
                ['sku' => 'ITEM-001'],
            ],
        ]);

        $response
            ->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthorized tracker request.',
            ]);
    }
}
