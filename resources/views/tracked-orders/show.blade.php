<x-layouts.tracker title="Tracked Order Details" topbar-title="Tracked Order Details">
    <div class="page-head">
        <div>
            <div class="eyebrow">
                <span style="width:8px;height:8px;border-radius:50%;background:#2f80ed;display:inline-block;"></span>
                Synced From Storix
            </div>
            <h1 class="page-title">Order #{{ $trackedOrder->storix_order_id }}</h1>
            <p class="page-subtitle">Detailed tracker record, including payment refs, item snapshot, and lifecycle timestamps.</p>
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
                            <div class="detail-item-label">Total Price</div>
                            <div class="detail-item-value mono">PHP {{ number_format((float) $trackedOrder->total_price, 2) }}</div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-item-label">Xendit Invoice</div>
                            <div class="detail-item-value">{{ $trackedOrder->xendit_invoice_id ?: 'Not available' }}</div>
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
