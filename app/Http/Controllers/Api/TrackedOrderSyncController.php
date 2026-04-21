<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackedOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'xendit_invoice_id' => ['nullable', 'string', 'max:255'],
            'xendit_invoice_url' => ['nullable', 'string', 'max:255'],
            'xendit_payment_method' => ['nullable', 'string', 'max:255'],
            'xendit_reference_id' => ['nullable', 'string', 'max:255'],
            'prgc_ref' => ['nullable', 'string', 'max:255'],
            'pickup_address' => ['nullable', 'array'],
            'delivery_address' => ['nullable', 'array'],
            'placed_at' => ['nullable', 'date'],
            'approved_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'payment_paid_at' => ['nullable', 'date'],
            'payment_expires_at' => ['nullable', 'date'],
        ]);

        $trackedOrder = TrackedOrder::updateOrCreate(
            ['storix_order_id' => $validated['storix_order_id']],
            $this->normalizeTrackedOrderAttributes([
                'storix_user_id' => $validated['storix_user_id'],
                'status' => $validated['status'] ?? 'pending',
                'total_price' => $validated['total_price'],
                'items' => $validated['items'],
                'payment_status' => $validated['payment_status'] ?? 'unpaid',
                'payment_method' => $validated['payment_method'] ?? null,
                'payment_amount' => $validated['payment_amount'] ?? $validated['total_price'],
                'xendit_invoice_id' => $validated['xendit_invoice_id'] ?? null,
                'xendit_invoice_url' => $validated['xendit_invoice_url'] ?? null,
                'xendit_payment_method' => $validated['xendit_payment_method'] ?? null,
                'xendit_reference_id' => $validated['xendit_reference_id'] ?? null,
                'prgc_ref' => $validated['prgc_ref'] ?? null,
                'pickup_address' => $validated['pickup_address'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
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
            'payment_method' => ['nullable', 'string', 'max:255'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'approved_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'payment_paid_at' => ['nullable', 'date'],
            'payment_expires_at' => ['nullable', 'date'],
            'xendit_invoice_id' => ['nullable', 'string', 'max:255'],
            'xendit_invoice_url' => ['nullable', 'string', 'max:255'],
            'xendit_payment_method' => ['nullable', 'string', 'max:255'],
            'xendit_reference_id' => ['nullable', 'string', 'max:255'],
            'prgc_ref' => ['nullable', 'string', 'max:255'],
            'pickup_address' => ['nullable', 'array'],
            'delivery_address' => ['nullable', 'array'],
        ]);

        $trackedOrder = TrackedOrder::query()
            ->where('storix_order_id', $storix_order_id)
            ->firstOrFail();

        $trackedOrder->fill($this->normalizeTrackedOrderAttributes([
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'] ?? $trackedOrder->payment_status,
            'payment_method' => $validated['payment_method'] ?? $trackedOrder->payment_method,
            'payment_amount' => $validated['payment_amount'] ?? $trackedOrder->payment_amount,
            'approved_at' => $validated['approved_at'] ?? $trackedOrder->approved_at,
            'completed_at' => $validated['completed_at'] ?? $trackedOrder->completed_at,
            'payment_paid_at' => $validated['payment_paid_at'] ?? $trackedOrder->payment_paid_at,
            'payment_expires_at' => $validated['payment_expires_at'] ?? $trackedOrder->payment_expires_at,
            'xendit_invoice_id' => $validated['xendit_invoice_id'] ?? $trackedOrder->xendit_invoice_id,
            'xendit_invoice_url' => $validated['xendit_invoice_url'] ?? $trackedOrder->xendit_invoice_url,
            'xendit_payment_method' => $validated['xendit_payment_method'] ?? $trackedOrder->xendit_payment_method,
            'xendit_reference_id' => $validated['xendit_reference_id'] ?? $trackedOrder->xendit_reference_id,
            'prgc_ref' => $validated['prgc_ref'] ?? $trackedOrder->prgc_ref,
        ]);
        $trackedOrder->save();

        return new JsonResponse([
            'message' => 'Tracked order status updated successfully.',
            'data' => $trackedOrder->fresh(),
        ]);
    }

    private function normalizeTrackedOrderAttributes(array $attributes, ?TrackedOrder $trackedOrder = null): array
    {
        if (! $this->isXenditManagedPayment($attributes, $trackedOrder)) {
            return $attributes;
        }

        $incomingPaymentStatus = $attributes['payment_status'] ?? $trackedOrder?->payment_status ?? 'unpaid';
        $currentPaymentStatus = $trackedOrder?->payment_status;

        if ($currentPaymentStatus === 'paid' && in_array($incomingPaymentStatus, ['pending', 'unpaid', 'failed', 'expired'], true)) {
            $attributes['payment_status'] = $currentPaymentStatus;
            $attributes['status'] = $trackedOrder->status;
            $attributes['approved_at'] = $trackedOrder->approved_at;
            $attributes['payment_paid_at'] = $trackedOrder->payment_paid_at;

            return $attributes;
        }

        if ($incomingPaymentStatus === 'paid') {
            $paidAt = $this->normalizeTimestamp(
                $attributes['payment_paid_at'] ?? $trackedOrder?->payment_paid_at ?? now()
            );

            $attributes['payment_paid_at'] = $paidAt;
            $attributes['approved_at'] = $this->normalizeTimestamp(
                $attributes['approved_at'] ?? $trackedOrder?->approved_at ?? $paidAt
            );

            if (! in_array($attributes['status'] ?? $trackedOrder?->status, ['processing', 'completed', 'cancelled'], true)) {
                $attributes['status'] = 'approved';
            }

            return $attributes;
        }

        if (($attributes['status'] ?? $trackedOrder?->status) === 'approved') {
            $attributes['status'] = 'pending';
        }

        if ($currentPaymentStatus !== 'paid') {
            $attributes['approved_at'] = null;
        }

        return $attributes;
    }

    private function isXenditManagedPayment(array $attributes, ?TrackedOrder $trackedOrder = null): bool
    {
        $paymentMethod = strtolower((string) ($attributes['payment_method'] ?? $trackedOrder?->payment_method ?? ''));
        $xenditFields = [
            $attributes['xendit_invoice_id'] ?? $trackedOrder?->xendit_invoice_id,
            $attributes['xendit_invoice_url'] ?? $trackedOrder?->xendit_invoice_url,
            $attributes['xendit_payment_method'] ?? $trackedOrder?->xendit_payment_method,
            $attributes['xendit_reference_id'] ?? $trackedOrder?->xendit_reference_id,
        ];

        return collect($xenditFields)->filter()->isNotEmpty()
            || str_contains($paymentMethod, 'xendit')
            || in_array($paymentMethod, ['gcash', 'maya', 'paymaya', 'qrph', 'shopeepay'], true);
    }

    private function normalizeTimestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }
}
