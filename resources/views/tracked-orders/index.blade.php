<x-layouts.tracker title="Tracker Dashboard" topbar-title="Tracking Dashboard">
    <div class="page-head">
        <div>
            <div class="eyebrow">
                <span style="width:8px;height:8px;border-radius:50%;background:#00d4aa;display:inline-block;"></span>
                Storix Sync Overview
            </div>
            <h1 class="page-title">Tracked Orders</h1>
            <p class="page-subtitle">Live order records coming from Storix, stored independently inside the Tracker database.</p>
        </div>

        <div class="hero-card">
            <div class="hero-label">Tracker Database</div>
            <div class="hero-value">{{ number_format($stats['total']) }}</div>
            <div class="hero-note">orders synced from Storix and ready for monitoring</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div class="stat-accent accent-total"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
            <div class="stat-accent accent-pending"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format($stats['approved']) }}</div>
            <div class="stat-accent accent-approved"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ number_format($stats['completed']) }}</div>
            <div class="stat-accent accent-completed"></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Cancelled</div>
            <div class="stat-value">{{ number_format($stats['cancelled']) }}</div>
            <div class="stat-accent accent-cancelled"></div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Tracker Order Stream</div>
            <div class="muted">{{ $orders->total() }} total result{{ $orders->total() === 1 ? '' : 's' }}</div>
        </div>

        <div class="filters">
            <div class="pill-group">
                <a href="{{ route('tracked-orders.index') }}" class="pill {{ $currentStatus === null ? 'active' : '' }}">All</a>
                <a href="{{ route('tracked-orders.index', ['status' => 'pending', 'search' => $search]) }}" class="pill {{ $currentStatus === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('tracked-orders.index', ['status' => 'approved', 'search' => $search]) }}" class="pill {{ $currentStatus === 'approved' ? 'active' : '' }}">Approved</a>
                <a href="{{ route('tracked-orders.index', ['status' => 'completed', 'search' => $search]) }}" class="pill {{ $currentStatus === 'completed' ? 'active' : '' }}">Completed</a>
                <a href="{{ route('tracked-orders.index', ['status' => 'cancelled', 'search' => $search]) }}" class="pill {{ $currentStatus === 'cancelled' ? 'active' : '' }}">Cancelled</a>
            </div>

            <form method="GET" action="{{ route('tracked-orders.index') }}" class="search-form">
                @if($currentStatus)
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" class="search-input" placeholder="Search order id, user id, Xendit, PRGC">
                <button type="submit" class="btn btn-primary">Search</button>
                @if($search || $currentStatus)
                    <a href="{{ route('tracked-orders.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        @if($orders->count())
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Storix Order</th>
                            <th>User</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Placed</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td>
                                    <div class="primary mono">#{{ $order->storix_order_id }}</div>
                                    <div class="muted" style="font-size:0.75rem;">Tracker #{{ $order->id }}</div>
                                </td>
                                <td>
                                    <div class="primary">User #{{ $order->storix_user_id }}</div>
                                    <div class="muted" style="font-size:0.75rem;">{{ $order->prgc_ref ?: 'No PRGC ref yet' }}</div>
                                </td>
                                <td>{{ count($order->items ?? []) }} item{{ count($order->items ?? []) === 1 ? '' : 's' }}</td>
                                <td class="mono">PHP {{ number_format((float) $order->total_price, 2) }}</td>
                                <td>
                                    @php($statusClass = in_array($order->status, ['pending', 'approved', 'completed', 'cancelled', 'processing']) ? $order->status : 'pending')
                                    <span class="badge badge-{{ $statusClass }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>
                                    <div class="primary">{{ ucfirst($order->payment_status) }}</div>
                                    <div class="muted" style="font-size:0.75rem;">{{ $order->xendit_invoice_id ?: 'No Xendit invoice' }}</div>
                                </td>
                                <td>
                                    <div class="primary">{{ optional($order->placed_at)->format('M d, Y') ?: '-' }}</div>
                                    <div class="muted" style="font-size:0.75rem;">{{ optional($order->placed_at)->format('H:i') ?: 'Waiting sync' }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('tracked-orders.show', $order) }}" class="action-link">View details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="pagination">{{ $orders->links() }}</div>
            @endif
        @else
            <div class="empty">
                <div style="font-size:1rem;font-weight:700;color:#0c1a14;">No tracked orders yet</div>
                <div style="margin-top:0.45rem;">Create an order in Storix and it should appear here after the API sync succeeds.</div>
            </div>
        @endif
    </div>
</x-layouts.tracker>
