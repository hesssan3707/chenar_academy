<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductAccess;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);

        $subtotal = (int) $items->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);

        $couponCode = (string) $request->session()->get('checkout_coupon_code', '');
        $coupon = $couponCode !== '' ? $this->findValidCouponForUser($request, $couponCode) : null;

        $discountAmount = $coupon ? $this->calculateDiscountAmount($subtotal, $coupon) : 0;
        $discountAmount = min($discountAmount, $subtotal);

        $total = max(0, $subtotal - $discountAmount);

        return view('commerce.checkout.index', [
            'cart' => $cart,
            'items' => $items,
            'couponCode' => $couponCode,
            'coupon' => $coupon,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'payableAmount' => $total,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $raw = trim((string) ($validated['code'] ?? ''));
        $code = strtoupper($raw);

        if ($code === '') {
            $request->session()->forget('checkout_coupon_code');

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'success',
                'title' => 'حذف شد',
                'message' => 'کد تخفیف حذف شد.',
            ]);
        }

        $coupon = $this->findValidCouponForUser($request, $code);
        if (! $coupon) {
            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'error',
                'title' => 'نامعتبر',
                'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
            ]);
        }

        $request->session()->put('checkout_coupon_code', $code);

        return redirect()->route('checkout.index')->with('toast', [
            'type' => 'success',
            'title' => 'اعمال شد',
            'message' => 'کد تخفیف اعمال شد.',
        ]);
    }

    public function pay(Request $request): RedirectResponse
    {
        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);

        if (! $cart || $items->isEmpty()) {
            return redirect()->route('cart.index')->with('toast', [
                'type' => 'error',
                'title' => 'سبد خرید خالی است',
                'message' => 'برای پرداخت ابتدا یک محصول به سبد خرید اضافه کنید.',
            ]);
        }

        $subtotal = (int) $items->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);

        $couponCode = (string) $request->session()->get('checkout_coupon_code', '');
        $coupon = $couponCode !== '' ? $this->findValidCouponForUser($request, $couponCode) : null;

        $discountAmount = $coupon ? $this->calculateDiscountAmount($subtotal, $coupon) : 0;
        $discountAmount = min($discountAmount, $subtotal);

        $total = max(0, $subtotal - $discountAmount);

        $payment = DB::transaction(function () use ($request, $cart, $items, $subtotal, $discountAmount, $total, $coupon, $couponCode) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'currency' => 'IRR',
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $total,
                'payable_amount' => $total,
                'placed_at' => now(),
                'paid_at' => null,
                'cancelled_at' => null,
                'meta' => [
                    'cart_id' => $cart->id,
                    'coupon_code' => $coupon ? $couponCode : null,
                    'coupon_id' => $coupon?->id,
                ],
            ]);

            foreach ($items as $item) {
                $product = $item->product;
                if (! $product) {
                    continue;
                }

                $qty = max(1, (int) $item->quantity);
                $unit = max(0, (int) $item->unit_price);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_type' => (string) $product->type,
                    'product_title' => (string) $product->title,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'total_price' => $unit * $qty,
                    'currency' => $product->currency ?? 'IRR',
                    'meta' => [],
                ]);
            }

            return Payment::query()->create([
                'order_id' => $order->id,
                'gateway' => app()->environment('production') ? 'gateway' : 'mock',
                'status' => 'initiated',
                'amount' => $total,
                'currency' => 'IRR',
                'authority' => null,
                'reference_id' => null,
                'paid_at' => null,
                'meta' => [],
            ]);
        });

        if (! app()->environment('production')) {
            return redirect()->route('checkout.mock-gateway.show', $payment->id);
        }

        abort(501);
    }

    public function mockGateway(Request $request, Payment $payment): View
    {
        $payment->loadMissing('order.items');

        abort_if(! $payment->order || (int) $payment->order->user_id !== (int) $request->user()->id, 404);
        abort_if((string) $payment->gateway !== 'mock', 404);

        return view('commerce.checkout.mock-gateway', [
            'payment' => $payment,
            'order' => $payment->order,
        ]);
    }

    public function mockGatewayReturn(Request $request, Payment $payment): RedirectResponse
    {
        $validated = $request->validate([
            'result' => ['required', 'string', 'in:success,fail'],
        ]);

        $payment->loadMissing('order.items');

        abort_if(! $payment->order || (int) $payment->order->user_id !== (int) $request->user()->id, 404);
        abort_if((string) $payment->gateway !== 'mock', 404);
        abort_if((string) $payment->status !== 'initiated', 403);

        if ($validated['result'] !== 'success') {
            $payment->forceFill([
                'status' => 'failed',
            ])->save();

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'error',
                'title' => 'پرداخت ناموفق',
                'message' => 'پرداخت انجام نشد. می‌توانید دوباره تلاش کنید.',
            ]);
        }

        $expirationDays = $this->accessExpirationDays();

        DB::transaction(function () use ($payment, $expirationDays) {
            $order = $payment->order;

            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
                'reference_id' => 'MOCK-'.now()->format('YmdHis').'-'.random_int(1000, 9999),
            ])->save();

            $order->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
            ])->save();

            $couponId = (int) ($order->meta['coupon_id'] ?? 0);
            if ($couponId > 0 && $coupon = Coupon::query()->find($couponId)) {
                CouponRedemption::query()->firstOrCreate([
                    'coupon_id' => $coupon->id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                ], [
                    'redeemed_at' => now(),
                ]);

                $coupon->increment('used_count');
            }

            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $expiresAt = null;
                if ($expirationDays > 0) {
                    $expiresAt = now()->addDays($expirationDays);
                }

                ProductAccess::query()->firstOrCreate([
                    'user_id' => $order->user_id,
                    'product_id' => $item->product_id,
                ], [
                    'order_item_id' => $item->id,
                    'granted_at' => now(),
                    'expires_at' => $expiresAt,
                    'meta' => [],
                ]);
            }

            $cartId = (int) ($order->meta['cart_id'] ?? 0);
            if ($cartId > 0) {
                Cart::query()->where('id', $cartId)->where('user_id', $order->user_id)->update([
                    'status' => 'checked_out',
                ]);

                CartItem::query()->where('cart_id', $cartId)->delete();
            }
        });

        $request->session()->forget('checkout_coupon_code');

        return redirect()->route('panel.library.index')->with('toast', [
            'type' => 'success',
            'title' => 'پرداخت موفق',
            'message' => 'پرداخت با موفقیت انجام شد و دسترسی شما فعال شد.',
        ]);
    }

    private function accessExpirationDays(): int
    {
        if (! Schema::hasTable('settings')) {
            return 0;
        }

        $setting = Setting::query()->where('key', 'commerce.access_expiration_days')->first();
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

    private function findActiveUserCart(Request $request): ?Cart
    {
        return Cart::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    private function getCartItems(?Cart $cart)
    {
        if (! $cart) {
            return collect();
        }

        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->with('product')
            ->orderBy('id')
            ->get();
    }

    private function findValidCouponForUser(Request $request, string $code): ?Coupon
    {
        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return null;
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return null;
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            return null;
        }

        if ($coupon->usage_limit !== null && (int) $coupon->used_count >= (int) $coupon->usage_limit) {
            return null;
        }

        if ($coupon->per_user_limit !== null) {
            $usedByUser = CouponRedemption::query()
                ->where('coupon_id', $coupon->id)
                ->where('user_id', $request->user()->id)
                ->count();

            if ($usedByUser >= (int) $coupon->per_user_limit) {
                return null;
            }
        }

        return $coupon;
    }

    private function calculateDiscountAmount(int $subtotal, Coupon $coupon): int
    {
        $type = strtolower((string) $coupon->discount_type);
        $value = (int) $coupon->discount_value;

        if (in_array($type, ['percent', 'percentage'], true)) {
            $percent = max(0, min(100, $value));

            return (int) floor($subtotal * $percent / 100);
        }

        return max(0, $value);
    }

    private function generateOrderNumber(): string
    {
        for ($i = 0; $i < 5; $i++) {
            $orderNumber = 'ORD-'.now()->format('YmdHis').'-'.random_int(1000, 9999);

            if (! Order::query()->where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }

        return 'ORD-'.now()->format('YmdHis').'-'.random_int(100000, 999999);
    }
}
