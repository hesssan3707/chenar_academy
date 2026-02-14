<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductAccess;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);

        $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
        $invoice = $this->invoiceData($request, $items, $currency);

        return view('commerce.checkout.index', [
            'cart' => $cart,
            'items' => $items,
            ...$invoice,
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $isAjax = $request->expectsJson() || strtolower((string) $request->header('X-Requested-With')) === 'xmlhttprequest';

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        $raw = trim((string) ($validated['code'] ?? ''));
        $code = strtoupper($raw);

        if ($code === '') {
            $request->session()->forget('checkout_coupon_code');

            if ($isAjax) {
                $cart = $this->findActiveUserCart($request);
                $items = $this->getCartItems($cart);
                $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
                $invoice = $this->invoiceData($request, $items, $currency);

                return response()->json([
                    'ok' => true,
                    'type' => 'success',
                    'message' => 'کد تخفیف حذف شد.',
                    ...$this->invoicePayload($invoice),
                ]);
            }

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'success',
                'title' => 'حذف شد',
                'message' => 'کد تخفیف حذف شد.',
            ]);
        }

        if (preg_match('/^[A-Z0-9]{5,8}$/', $code) !== 1) {
            if ($isAjax) {
                $cart = $this->findActiveUserCart($request);
                $items = $this->getCartItems($cart);
                $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
                $invoice = $this->invoiceData($request, $items, $currency);

                return response()->json([
                    'ok' => false,
                    'type' => 'error',
                    'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
                    ...$this->invoicePayload($invoice),
                ], 422);
            }

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'error',
                'title' => 'نامعتبر',
                'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
            ]);
        }

        $coupon = $this->findValidCouponForUser($request, $code);
        if (! $coupon) {
            if ($isAjax) {
                $cart = $this->findActiveUserCart($request);
                $items = $this->getCartItems($cart);
                $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
                $invoice = $this->invoiceData($request, $items, $currency);

                return response()->json([
                    'ok' => false,
                    'type' => 'error',
                    'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
                    ...$this->invoicePayload($invoice),
                ], 422);
            }

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'error',
                'title' => 'نامعتبر',
                'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
            ]);
        }

        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);
        if (! $this->couponAppliesToItems($items, $coupon)) {
            if ($isAjax) {
                $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
                $invoice = $this->invoiceData($request, $items, $currency);

                return response()->json([
                    'ok' => false,
                    'type' => 'error',
                    'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
                    ...$this->invoicePayload($invoice),
                ], 422);
            }

            return redirect()->route('checkout.index')->with('toast', [
                'type' => 'error',
                'title' => 'نامعتبر',
                'message' => 'کد تخفیف نامعتبر است یا شرایط استفاده را ندارد.',
            ]);
        }

        $request->session()->put('checkout_coupon_code', $code);

        if ($isAjax) {
            $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
            $invoice = $this->invoiceData($request, $items, $currency);

            return response()->json([
                'ok' => true,
                'type' => 'success',
                'message' => 'کد تخفیف اعمال شد.',
                ...$this->invoicePayload($invoice),
            ]);
        }

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

        $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
        $invoice = $this->invoiceData($request, $items, $currency);
        $subtotal = (int) $invoice['subtotal'];
        $discountAmount = (int) $invoice['discountAmount'];
        $taxPercent = (int) $invoice['taxPercent'];
        $taxAmount = (int) $invoice['taxAmount'];
        $total = (int) $invoice['payableAmount'];
        $coupon = $invoice['coupon'] ?? null;
        $couponCode = (string) ($invoice['couponCode'] ?? '');

        $payment = DB::transaction(function () use ($request, $cart, $items, $subtotal, $discountAmount, $total, $coupon, $couponCode, $taxPercent, $taxAmount, $currency) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'currency' => $currency,
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
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
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
                    'currency' => $currency,
                    'meta' => [],
                ]);
            }

            return Payment::query()->create([
                'order_id' => $order->id,
                'gateway' => app()->environment('production') ? 'gateway' : 'mock',
                'status' => 'initiated',
                'amount' => $total,
                'currency' => $currency,
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

    public function cardToCard(Request $request): View
    {
        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);

        $cards = [];
        if (Schema::hasTable('settings')) {
            $cards = [
                [
                    'name' => $this->settingString('commerce.card_to_card.card1.name'),
                    'number' => $this->settingString('commerce.card_to_card.card1.number'),
                ],
                [
                    'name' => $this->settingString('commerce.card_to_card.card2.name'),
                    'number' => $this->settingString('commerce.card_to_card.card2.number'),
                ],
            ];

            $cards = array_values(array_filter($cards, function (array $card) {
                return trim((string) ($card['number'] ?? '')) !== '';
            }));
        }

        if (! $cart || $items->isEmpty()) {
            return view('commerce.checkout.card-to-card', [
                'cart' => $cart,
                'items' => $items,
                'cardToCardCards' => $cards,
                ...$this->invoiceData($request, $items, strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()))),
            ]);
        }

        return view('commerce.checkout.card-to-card', [
            'cart' => $cart,
            'items' => $items,
            'cardToCardCards' => $cards,
            ...$this->invoiceData($request, $items, strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()))),
        ]);
    }

    public function cardToCardStore(Request $request): RedirectResponse
    {
        $cart = $this->findActiveUserCart($request);
        $items = $this->getCartItems($cart);

        if (! $cart || $items->isEmpty()) {
            return redirect()->route('cart.index')->with('toast', [
                'type' => 'error',
                'title' => 'سبد خرید خالی است',
                'message' => 'برای ثبت سفارش ابتدا یک محصول به سبد خرید اضافه کنید.',
            ]);
        }

        $validated = $request->validate([
            'receipt' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $receiptFile = $validated['receipt'] instanceof UploadedFile ? $validated['receipt'] : null;
        if (! $receiptFile) {
            return redirect()->back()->withInput()->withErrors(['receipt' => 'رسید پرداخت معتبر نیست.']);
        }

        $currency = strtoupper((string) ($cart?->currency ?: $this->commerceCurrency()));
        $invoice = $this->invoiceData($request, $items, $currency);
        $subtotal = (int) $invoice['subtotal'];
        $discountAmount = (int) $invoice['discountAmount'];
        $taxPercent = (int) $invoice['taxPercent'];
        $taxAmount = (int) $invoice['taxAmount'];
        $total = (int) $invoice['payableAmount'];
        $coupon = $invoice['coupon'] ?? null;
        $couponCode = (string) ($invoice['couponCode'] ?? '');

        $media = $this->storeUploadedMedia($receiptFile, 'local', 'receipts');

        $order = DB::transaction(function () use ($request, $cart, $items, $subtotal, $discountAmount, $total, $coupon, $couponCode, $taxPercent, $taxAmount, $media, $currency) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $request->user()->id,
                'status' => 'pending_review',
                'currency' => $currency,
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
                    'tax_percent' => $taxPercent,
                    'tax_amount' => $taxAmount,
                    'payment_method' => 'card_to_card',
                    'receipt_media_id' => $media?->id,
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
                    'currency' => $currency,
                    'meta' => [],
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'gateway' => 'card_to_card',
                'status' => 'pending_review',
                'amount' => $total,
                'currency' => $currency,
                'authority' => null,
                'reference_id' => null,
                'paid_at' => null,
                'meta' => [
                    'receipt_media_id' => $media?->id,
                ],
            ]);

            Cart::query()->where('id', $cart->id)->where('user_id', $request->user()->id)->update([
                'status' => 'checked_out',
            ]);
            CartItem::query()->where('cart_id', $cart->id)->delete();

            return $order;
        });

        $request->session()->forget('checkout_coupon_code');

        return redirect()->route('panel.orders.show', $order->id)->with('toast', [
            'type' => 'success',
            'title' => 'ثبت شد',
            'message' => 'سفارش شما ثبت شد و پس از بررسی رسید تایید خواهد شد.',
        ]);
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

    private function invoiceData(Request $request, $items, ?string $currency = null): array
    {
        $subtotal = (int) ($items ?? collect())->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);

        $couponCode = (string) $request->session()->get('checkout_coupon_code', '');
        $coupon = $couponCode !== '' ? $this->findValidCouponForUser($request, $couponCode) : null;

        $eligibleSubtotal = 0;
        if ($coupon) {
            $eligibleSubtotal = $this->couponEligibleSubtotal($items, $coupon);
            if ($eligibleSubtotal <= 0 && ! $coupon->appliesToAllProducts()) {
                $request->session()->forget('checkout_coupon_code');
                $couponCode = '';
                $coupon = null;
            }
        }

        $discountAmount = $coupon ? $this->calculateDiscountAmount($eligibleSubtotal, $coupon) : 0;
        $discountAmount = min($discountAmount, max(0, $eligibleSubtotal));

        $netTotal = max(0, $subtotal - $discountAmount);
        $taxPercent = $this->commerceTaxPercent();
        $taxAmount = (int) floor($netTotal * $taxPercent / 100);
        $total = $netTotal + $taxAmount;

        $currency = strtoupper((string) ($currency ?: $this->commerceCurrency()));

        return [
            'couponCode' => $couponCode,
            'coupon' => $coupon,
            'subtotal' => $subtotal,
            'discountAmount' => $discountAmount,
            'taxPercent' => $taxPercent,
            'taxAmount' => $taxAmount,
            'payableAmount' => $total,
            'currencyUnit' => $currency === 'IRT' ? 'تومان' : 'ریال',
        ];
    }

    private function invoicePayload(array $invoice): array
    {
        return [
            'couponCode' => (string) ($invoice['couponCode'] ?? ''),
            'subtotal' => (int) ($invoice['subtotal'] ?? 0),
            'discountAmount' => (int) ($invoice['discountAmount'] ?? 0),
            'taxPercent' => (int) ($invoice['taxPercent'] ?? 0),
            'taxAmount' => (int) ($invoice['taxAmount'] ?? 0),
            'payableAmount' => (int) ($invoice['payableAmount'] ?? 0),
            'currencyUnit' => (string) ($invoice['currencyUnit'] ?? ''),
        ];
    }

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): ?Media
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($directory, $disk);
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
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
        $cart = Cart::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if ($cart) {
            $this->ensureCartCurrency($cart);
        }

        return $cart;
    }

    private function getCartItems(?Cart $cart)
    {
        if (! $cart) {
            return collect();
        }

        $items = CartItem::query()
            ->where('cart_id', $cart->id)
            ->with('product')
            ->orderBy('id')
            ->get();

        $currency = strtoupper((string) ($cart->currency ?: $this->commerceCurrency()));
        $this->normalizeCartItemsCurrencyAndPrice($items, $currency);

        return $items;
    }

    private function ensureCartCurrency(Cart $cart): string
    {
        $currency = $this->commerceCurrency();
        $current = strtoupper((string) ($cart->currency ?? ''));

        if ($current !== $currency) {
            $cart->forceFill([
                'currency' => $currency,
            ])->save();
        }

        return $currency;
    }

    private function normalizeCartItemsCurrencyAndPrice($items, string $currency): void
    {
        foreach (($items ?? collect()) as $item) {
            if (! $item instanceof CartItem) {
                continue;
            }

            $product = $item->product;
            $desiredCurrency = $currency;
            $desiredUnitPrice = $product ? (int) $product->displayFinalPrice($currency) : (int) ($item->unit_price ?? 0);

            if ((int) ($item->unit_price ?? 0) !== $desiredUnitPrice || strtoupper((string) ($item->currency ?? '')) !== $desiredCurrency) {
                $item->forceFill([
                    'unit_price' => $desiredUnitPrice,
                    'currency' => $desiredCurrency,
                ])->save();
            }
        }
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

        $userId = (int) ($request->user()?->id ?? 0);
        if ($userId <= 0) {
            return null;
        }

        if (Order::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'pending_review', 'paid'])
            ->where('meta->coupon_id', $coupon->id)
            ->exists()) {
            return null;
        }

        if (CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->where('user_id', $userId)
            ->exists()) {
            return null;
        }

        if ((bool) (($coupon->meta ?? [])['first_purchase_only'] ?? false)) {
            $hasPaidOrder = Order::query()
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->exists();

            if ($hasPaidOrder) {
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

    private function couponEligibleSubtotal($items, Coupon $coupon): int
    {
        $collection = $items ?? collect();
        if ($coupon->appliesToAllProducts()) {
            return (int) $collection->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);
        }

        $productIds = $coupon->productIds();
        if (count($productIds) === 0) {
            return (int) $collection->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);
        }

        return (int) $collection
            ->filter(fn (CartItem $item) => $item->product_id && in_array((int) $item->product_id, $productIds, true))
            ->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);
    }

    private function couponAppliesToItems($items, Coupon $coupon): bool
    {
        if ($coupon->appliesToAllProducts()) {
            return true;
        }

        $productIds = $coupon->productIds();
        if (count($productIds) === 0) {
            return true;
        }

        $collection = $items ?? collect();

        return $collection->contains(function (CartItem $item) use ($productIds) {
            return $item->product_id && in_array((int) $item->product_id, $productIds, true);
        });
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
