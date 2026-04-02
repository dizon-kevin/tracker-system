<x-layouts.tracker title="Tracked Order Details" topbar-title="Tracked Order Details">
    @php($pickup = $trackedOrder->pickup_address ?? [])
    @php($delivery = $trackedOrder->delivery_address ?? [])

    <div class="page-head">
        <div>
            <div class="eyebrow">
                <span style="width:8px;height:8px;border-radius:50%;background:#2f80ed;display:inline-block;"></span>
                Synced From Storix
            </div>
            <h1 class="page-title">Order #{{ $trackedOrder->storix_order_id }}</h1>
            <p class="page-subtitle">Detailed tracker record with payment references, address snapshot, and lifecycle timestamps.</p>
        </div>

        <div style="display:flex;gap:0.65rem;flex-wrap:wrap;">
            @php($statusClass = in_array($trackedOrder->status, ['pending', 'approved', 'completed', 'cancelled', 'processing']) ? $trackedOrder->status : 'pending')
            <span class="badge badge-{{ $statusClass }}">{{ ucfirst($trackedOrder->status) }}</span>
            <span class="badge badge-{{ $trackedOrder->payment_status === 'paid' ? 'completed' : ($trackedOrder->payment_status === 'failed' ? 'cancelled' : 'pending') }}">
                Payment {{ ucfirst($trackedOrder->payment_status) }}
            </span>
            <a href="{{ route('tracked-orders.index') }}" class="btn btn-secondary">Back to dashboard</a>
        </div>
    </div>

    <div class="grid-two">
        <div class="detail-stack">
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Order Snapshot</div>
                </div>
                <div style="padding:1.1rem 1.2rem;">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-item-label">Storix Order ID</div>
                            <div class="detail-item-value mono">#{{ $trackedOrder->storix_order_id }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Storix User ID</div>
                            <div class="detail-item-value">User #{{ $trackedOrder->storix_user_id }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Tracker Record</div>
                            <div class="detail-item-value mono">#{{ $trackedOrder->id }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Order Total</div>
                            <div class="detail-item-value mono">PHP {{ number_format((float) $trackedOrder->total_price, 2) }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Payment Method</div>
                            <div class="detail-item-value">{{ $trackedOrder->payment_method ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Payment Amount</div>
                            <div class="detail-item-value mono">PHP {{ number_format((float) ($trackedOrder->payment_amount ?? 0), 2) }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Xendit Invoice</div>
                            <div class="detail-item-value">{{ $trackedOrder->xendit_invoice_id ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Xendit Channel</div>
                            <div class="detail-item-value">{{ $trackedOrder->xendit_payment_method ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Reference ID</div>
                            <div class="detail-item-value">{{ $trackedOrder->xendit_reference_id ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Invoice URL</div>
                            <div class="detail-item-value" style="word-break:break-word;">
                                @if($trackedOrder->xendit_invoice_url)
                                    <a href="{{ $trackedOrder->xendit_invoice_url }}" target="_blank" rel="noopener">Open hosted payment page</a>
                                @else
                                    Not available
                                @endif
                            </div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">PRGC Reference</div>
                            <div class="detail-item-value">{{ $trackedOrder->prgc_ref ?: 'Not available' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Pickup and Delivery</div>
                </div>
                <div style="padding:1.1rem 1.2rem;">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <div class="detail-item-label">Pickup Address</div>
                            <div class="detail-item-value">{{ collect([$pickup['street_address'] ?? null, $pickup['barangay_name'] ?? null, $pickup['city_name'] ?? null, $pickup['province_name'] ?? null, $pickup['region_name'] ?? null])->filter()->implode(', ') ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Pickup Contact</div>
                            <div class="detail-item-value">{{ $pickup['contact_number'] ?? 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Delivery Address</div>
                            <div class="detail-item-value">{{ collect([$delivery['street_address'] ?? null, $delivery['barangay_name'] ?? null, $delivery['city_name'] ?? null, $delivery['province_name'] ?? null, $delivery['region_name'] ?? null])->filter()->implode(', ') ?: 'Not available' }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Delivery Contact</div>
                            <div class="detail-item-value">{{ $delivery['contact_number'] ?? 'Not available' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Synced Items</div>
                    <div class="muted">{{ $items->count() }} line item{{ $items->count() === 1 ? '' : 's' }}</div>
                </div>
                <div style="padding:1.1rem 1.2rem;">
                    @if($items->isNotEmpty())
                        <div class="item-list">
                            @foreach($items as $item)
                                <div class="item-card">
                                    <div>
                                        <div class="item-name">{{ $item['product_name'] ?? $item['name'] ?? 'Unnamed item' }}</div>
                                        <div class="item-meta">SKU: {{ $item['sku'] ?? 'N/A' }} | Product ID: {{ $item['product_id'] ?? 'N/A' }}</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div class="primary">Qty {{ $item['quantity'] ?? 0 }}</div>
                                        <div class="item-meta">PHP {{ number_format((float) ($item['total_price'] ?? $item['price'] ?? 0), 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="muted">No item snapshot was included in the sync payload yet.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="detail-stack">
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Timeline</div>
                </div>
                <div style="padding:1.1rem 1.2rem;">
                    <div class="timeline">
                        @foreach($timeline as $step)
                            <div class="timeline-item">
                                <div class="timeline-dot {{ $step['state'] }}"></div>
                                <div>
                                    <div class="timeline-label">{{ $step['label'] }}</div>
                                    <div class="timeline-time">{{ $step['timestamp'] ? $step['timestamp']->format('M d, Y h:i A') : 'Waiting for sync update' }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">Record Metadata</div>
                </div>
                <div style="padding:1.1rem 1.2rem;">
                    <div class="detail-item" style="margin-bottom:0.8rem;">
                        <div class="detail-item-label">Created in Tracker</div>
                        <div class="detail-item-value">{{ $trackedOrder->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="detail-item" style="margin-bottom:0.8rem;">
                        <div class="detail-item-label">Payment Paid At</div>
                        <div class="detail-item-value">{{ $trackedOrder->payment_paid_at?->format('M d, Y h:i A') ?? 'Waiting for payment confirmation' }}</div>
                    </div>
                    <div class="detail-item" style="margin-bottom:0.8rem;">
                        <div class="detail-item-label">Payment Expires At</div>
                        <div class="detail-item-value">{{ $trackedOrder->payment_expires_at?->format('M d, Y h:i A') ?? 'No expiry received yet' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-item-label">Last Tracker Update</div>
                        <div class="detail-item-value">{{ $trackedOrder->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="footer-note">This page reads from the Tracker database only. Storix remains the source system, while Tracker stores its own synced copy for monitoring.</div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.tracker>
