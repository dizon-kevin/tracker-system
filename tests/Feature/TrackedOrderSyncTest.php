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
