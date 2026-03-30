<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackedOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class TrackedOrderSyncController extends Controller
{
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'storix_order_id' => ['required', 'integer'],
            'storix_user_id' => ['required', 'integer'],
            'status' => ['nullable', 'string', Rule::in(config('tracker.allowed_statuses'))],
            'total_price' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'payment_status' => ['nullable', 'string', Rule::in(config('tracker.allowed_payment_statuses'))],
            'xendit_invoice_id' => ['nullable', 'string', 'max:255'],
            'prgc_ref' => ['nullable', 'string', 'max:255'],
            'placed_at' => ['nullable', 'date'],
            'approved_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
        ]);

        $trackedOrder = TrackedOrder::updateOrCreate(
            ['storix_order_id' => $validated['storix_order_id']],
            [
                'storix_user_id' => $validated['storix_user_id'],
                'status' => $validated['status'] ?? 'pending',
                'total_price' => $validated['total_price'],
                'items' => $validated['items'],
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'xendit_invoice_id' => $validated['xendit_invoice_id'] ?? null,
                'prgc_ref' => $validated['prgc_ref'] ?? null,
                'placed_at' => $validated['placed_at'] ?? null,
                'approved_at' => $validated['approved_at'] ?? null,
                'completed_at' => $validated['completed_at'] ?? null,
            ]
        );

        return new JsonResponse([
            'message' => $trackedOrder->wasRecentlyCreated
                ? 'Tracked order created successfully.'
                : 'Tracked order updated successfully.',
            'data' => $trackedOrder->fresh(),
        ], $trackedOrder->wasRecentlyCreated ? Response::HTTP_CREATED : Response::HTTP_OK);
    }

    public function updateStatus(Request $request, int $storix_order_id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(config('tracker.allowed_statuses'))],
            'payment_status' => ['nullable', 'string', Rule::in(config('tracker.allowed_payment_statuses'))],
            'approved_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'xendit_invoice_id' => ['nullable', 'string', 'max:255'],
            'prgc_ref' => ['nullable', 'string', 'max:255'],
        ]);

        $trackedOrder = TrackedOrder::query()
            ->where('storix_order_id', $storix_order_id)
            ->firstOrFail();

        $trackedOrder->fill([
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? $trackedOrder->payment_status,
            'approved_at' => $validated['approved_at'] ?? $trackedOrder->approved_at,
            'completed_at' => $validated['completed_at'] ?? $trackedOrder->completed_at,
            'xendit_invoice_id' => $validated['xendit_invoice_id'] ?? $trackedOrder->xendit_invoice_id,
            'prgc_ref' => $validated['prgc_ref'] ?? $trackedOrder->prgc_ref,
        ]);
        $trackedOrder->save();

        return new JsonResponse([
            'message' => 'Tracked order status updated successfully.',
            'data' => $trackedOrder->fresh(),
        ]);
    }
}
