<?php

namespace Tests\Feature;

use App\Models\TrackedOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackedOrderDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_the_tracker_dashboard(): void
    {
        $this->get('/')->assertRedirect('/tracked-orders');
    }

    public function test_tracker_dashboard_renders_synced_orders(): void
    {
        TrackedOrder::query()->create([
            'storix_order_id' => 2001,
            'storix_user_id' => 77,
            'status' => 'pending',
            'total_price' => 2500,
            'items' => [
                ['product_name' => 'Laptop Stand', 'quantity' => 1, 'total_price' => 2500],
            ],
            'payment_status' => 'unpaid',
            'placed_at' => now(),
        ]);

        $this->get('/tracked-orders')
            ->assertOk()
            ->assertSee('Tracked Orders')
            ->assertSee('#2001', false)
            ->assertSee('User #77');
    }

    public function test_tracker_detail_page_renders_item_snapshot(): void
    {
        $order = TrackedOrder::query()->create([
            'storix_order_id' => 2002,
            'storix_user_id' => 88,
            'status' => 'approved',
            'total_price' => 999,
            'items' => [
                ['product_name' => 'Barcode Scanner', 'sku' => 'SCN-01', 'quantity' => 1, 'total_price' => 999],
            ],
            'payment_status' => 'paid',
            'approved_at' => now(),
        ]);

        $this->get("/tracked-orders/{$order->id}")
            ->assertOk()
            ->assertSee('Order #2002')
            ->assertSee('Barcode Scanner')
            ->assertSee('SCN-01');
    }
}
