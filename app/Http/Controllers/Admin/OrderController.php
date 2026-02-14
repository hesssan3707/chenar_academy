<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Media;
use App\Models\Order;
use App\Models\ProductAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['payments', 'user'])
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.orders.index', [
            'title' => 'سفارش‌ها',
            'orders' => $orders,
        ]);
    }

    public function create(): View
    {
        return view('admin.orders.form', [
            'title' => 'ایجاد سفارش',
            'order' => new Order([
                'status' => 'pending',
                'currency' => $this->commerceCurrency(),
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'payable_amount' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.orders.index')->with('toast', [
            'type' => 'warning',
            'title' => 'ثبت سفارش',
            'message' => 'سفارش‌ها از طریق فرآیند پرداخت ایجاد می‌شوند.',
        ]);
    }

    public function show(int $order): View
    {
        $orderModel = Order::query()->with(['items', 'items.product', 'payments', 'user'])->findOrFail($order);

        $cardToCardPayment = ($orderModel->payments ?? collect())->firstWhere('gateway', 'card_to_card');
        $receiptMediaId = (int) (($cardToCardPayment?->meta ?? [])['receipt_media_id'] ?? 0);
        $receipt = $receiptMediaId > 0 ? Media::query()->find($receiptMediaId) : null;

        return view('admin.orders.show', [
            'title' => 'نمایش سفارش',
            'order' => $orderModel,
            'cardToCardPayment' => $cardToCardPayment,
            'cardToCardReceipt' => $receipt,
        ]);
    }

    public function edit(int $order): View
    {
        $orderModel = Order::query()->findOrFail($order);

        return view('admin.orders.form', [
            'title' => 'ویرایش سفارش',
            'order' => $orderModel,
        ]);
    }

    public function update(Request $request, int $order): RedirectResponse
    {
        $orderModel = Order::query()->findOrFail($order);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(['pending', 'pending_review', 'paid', 'rejected', 'cancelled'])],
        ]);

        $status = (string) $validated['status'];
        $orderModel->status = $status;

        if ($status === 'paid') {
            $orderModel->paid_at = $orderModel->paid_at ?: now();
            $orderModel->cancelled_at = null;
        } elseif ($status === 'cancelled') {
            $orderModel->cancelled_at = $orderModel->cancelled_at ?: now();
            $orderModel->paid_at = null;
        } else {
            $orderModel->paid_at = null;
            $orderModel->cancelled_at = null;
        }

        $orderModel->save();

        return redirect()->route('admin.orders.edit', $orderModel->id);
    }

    public function receiptCardToCard(int $order): Response
    {
        $orderModel = Order::query()->with('payments')->findOrFail($order);

        $payment = ($orderModel->payments ?? collect())->firstWhere('gateway', 'card_to_card');
        $receiptMediaId = (int) (($payment?->meta ?? [])['receipt_media_id'] ?? 0);
        abort_if($receiptMediaId <= 0, 404);

        $media = Media::query()->findOrFail($receiptMediaId);

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function approveCardToCard(Request $request, int $order): RedirectResponse
    {
        $orderModel = Order::query()->with(['items', 'payments'])->findOrFail($order);

        $payment = ($orderModel->payments ?? collect())->firstWhere('gateway', 'card_to_card');
        abort_if(! $payment, 404);

        if ((string) $orderModel->status !== 'pending_review' || (string) $payment->status !== 'pending_review') {
            return redirect()->route('admin.orders.show', $orderModel->id)->with('toast', [
                'type' => 'warning',
                'title' => 'امکان‌پذیر نیست',
                'message' => 'این سفارش در وضعیت قابل تایید نیست.',
            ]);
        }

        $expirationDays = $this->accessExpirationDays();

        DB::transaction(function () use ($orderModel, $payment, $expirationDays) {
            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'reference_id' => $payment->reference_id ?: 'C2C-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            ])->save();

            $orderModel->forceFill([
                'status' => 'paid',
                'paid_at' => $orderModel->paid_at ?: now(),
            ])->save();

            $couponId = (int) (($orderModel->meta ?? [])['coupon_id'] ?? 0);
            if ($couponId > 0 && $coupon = Coupon::query()->find($couponId)) {
                CouponRedemption::query()->firstOrCreate([
                    'coupon_id' => $coupon->id,
                    'user_id' => $orderModel->user_id,
                    'order_id' => $orderModel->id,
                ], [
                    'redeemed_at' => now(),
                ]);

                $coupon->increment('used_count');
            }

            foreach ($orderModel->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $expiresAt = null;
                if ($expirationDays > 0) {
                    $expiresAt = now()->addDays($expirationDays);
                }

                ProductAccess::query()->firstOrCreate([
                    'user_id' => $orderModel->user_id,
                    'product_id' => $item->product_id,
                ], [
                    'order_item_id' => $item->id,
                    'granted_at' => now(),
                    'expires_at' => $expiresAt,
                    'meta' => [],
                ]);
            }
        });

        return redirect()->route('admin.orders.show', $orderModel->id)->with('toast', [
            'type' => 'success',
            'title' => 'تایید شد',
            'message' => 'پرداخت کارت‌به‌کارت تایید شد و دسترسی کاربر فعال شد.',
        ]);
    }

    public function rejectCardToCard(int $order): RedirectResponse
    {
        $orderModel = Order::query()->with('payments')->findOrFail($order);

        $payment = ($orderModel->payments ?? collect())->firstWhere('gateway', 'card_to_card');
        abort_if(! $payment, 404);

        if ((string) $orderModel->status !== 'pending_review' || (string) $payment->status !== 'pending_review') {
            return redirect()->route('admin.orders.show', $orderModel->id)->with('toast', [
                'type' => 'warning',
                'title' => 'امکان‌پذیر نیست',
                'message' => 'این سفارش در وضعیت قابل رد کردن نیست.',
            ]);
        }

        DB::transaction(function () use ($orderModel, $payment) {
            $payment->forceFill([
                'status' => 'rejected',
            ])->save();

            $orderModel->forceFill([
                'status' => 'rejected',
            ])->save();
        });

        return redirect()->route('admin.orders.show', $orderModel->id)->with('toast', [
            'type' => 'success',
            'title' => 'رد شد',
            'message' => 'پرداخت کارت‌به‌کارت رد شد.',
        ]);
    }

    public function destroy(int $order): RedirectResponse
    {
        return redirect()->route('admin.orders.index')->with('toast', [
            'type' => 'warning',
            'title' => 'حذف سفارش',
            'message' => 'حذف سفارش در این نسخه فعال نیست.',
        ]);
    }

    private function accessExpirationDays(): int
    {
        if (! Schema::hasTable('settings')) {
            return 0;
        }

        $setting = \App\Models\Setting::query()->where('key', 'commerce.access_expiration_days')->first();
        if (! $setting) {
            return 0;
        }

        $value = $setting->value;

        if (is_int($value)) {
            return max(0, $value);
        }

        if (is_numeric($value)) {
            return max(0, (int) $value);
        }

        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return 0;
            }

            if (is_numeric($normalized)) {
                return max(0, (int) $normalized);
            }
        }

        return 0;
    }
}
