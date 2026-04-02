<?php

namespace App\Http\Controllers;

use App\Models\TrackedOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrackedOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = TrackedOrder::query()->latest('placed_at')->latest();

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('storix_order_id', 'like', "%{$search}%")
                    ->orWhere('storix_user_id', 'like', "%{$search}%")
                    ->orWhere('xendit_invoice_id', 'like', "%{$search}%")
                    ->orWhere('payment_method', 'like', "%{$search}%")
                    ->orWhere('prgc_ref', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(10)->withQueryString();
        $statsBase = TrackedOrder::query();

        return view('tracked-orders.index', [
            'orders' => $orders,
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'pending' => (clone $statsBase)->where('status', 'pending')->count(),
                'approved' => (clone $statsBase)->where('status', 'approved')->count(),
                'completed' => (clone $statsBase)->where('status', 'completed')->count(),
                'cancelled' => (clone $statsBase)->where('status', 'cancelled')->count(),
            ],
            'currentStatus' => $status !== '' ? $status : null,
            'search' => $search,
        ]);
    }

    public function show(TrackedOrder $trackedOrder): View
    {
        $timeline = collect([
            ['label' => 'Placed', 'timestamp' => $trackedOrder->placed_at, 'state' => $trackedOrder->placed_at ? 'done' : 'idle'],
            ['label' => 'Approved', 'timestamp' => $trackedOrder->approved_at, 'state' => $trackedOrder->approved_at ? 'done' : 'idle'],
            ['label' => 'Completed', 'timestamp' => $trackedOrder->completed_at, 'state' => $trackedOrder->completed_at ? 'done' : 'idle'],
        ]);

        if ($trackedOrder->status === 'cancelled') {
            $timeline->push([
                'label' => 'Cancelled',
                'timestamp' => $trackedOrder->updated_at,
                'state' => 'danger',
            ]);
        }

        return view('tracked-orders.show', [
            'trackedOrder' => $trackedOrder,
            'items' => collect($trackedOrder->items ?? []),
            'timeline' => $timeline,
        ]);
    }
}
