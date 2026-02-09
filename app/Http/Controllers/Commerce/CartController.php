<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Cookie;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $this->findCart($request);
        $items = collect();

        if ($cart) {
            $items = CartItem::query()
                ->where('cart_id', $cart->id)
                ->with('product')
                ->orderBy('id')
                ->get();
        }

        $subtotal = (int) $items->sum(fn (CartItem $item) => (int) $item->unit_price * (int) $item->quantity);

        return view('commerce.cart.index', [
            'cart' => $cart,
            'items' => $items,
            'subtotal' => $subtotal,
            'currencyUnit' => (($cart?->currency ?: $this->commerceCurrency()) === 'IRR')
                ? 'تومان'
                : ($cart?->currency ?: $this->commerceCurrency()),
        ]);
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::query()->where('status', 'published')->findOrFail($validated['product_id']);
        $quantity = 1;

        if ($request->user() && $product->userHasAccess($request->user())) {
            return redirect()->route('products.show', $product->slug)->with('toast', [
                'type' => 'error',
                'title' => 'قبلاً خریداری شده',
                'message' => 'این محصول قبلاً خریداری شده است و نیازی به افزودن دوباره ندارد.',
            ]);
        }

        $guestToken = $this->getOrCreateGuestCartToken($request);
        $request->session()->put('cart_token', $guestToken);
        $cart = $this->getOrCreateCart($request, $guestToken);

        $unitPrice = (int) $product->finalPrice();

        $item = CartItem::query()->where('cart_id', $cart->id)->where('product_id', $product->id)->first();
        if ($item) {
            $item->forceFill([
                'quantity' => 1,
                'unit_price' => $unitPrice,
                'currency' => $product->currency ?? $this->commerceCurrency(),
            ])->save();
        } else {
            CartItem::query()->create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'currency' => $product->currency ?? $this->commerceCurrency(),
                'meta' => [],
            ]);
        }

        $response = redirect()->route('cart.index')->with('toast', [
            'type' => 'success',
            'title' => 'به سبد اضافه شد',
            'message' => 'محصول به سبد خرید اضافه شد.',
        ]);

        if (! auth()->check() && $guestToken) {
            $response->withCookie($this->guestCartCookie($request, $guestToken));
        }

        return $response;
    }

    public function updateItem(Request $request, int $item): RedirectResponse
    {
        $cart = $this->findCart($request);
        if (! $cart) {
            return redirect()->route('cart.index');
        }

        $cartItem = CartItem::query()->where('id', $item)->where('cart_id', $cart->id)->firstOrFail();
        $cartItem->forceFill(['quantity' => 1])->save();

        return redirect()->route('cart.index')->with('toast', [
            'type' => 'success',
            'title' => 'به‌روزرسانی شد',
            'message' => 'سبد خرید به‌روزرسانی شد.',
        ]);
    }

    public function destroyItem(Request $request, int $item): RedirectResponse
    {
        $cart = $this->findCart($request);
        if (! $cart) {
            return redirect()->route('cart.index');
        }

        CartItem::query()->where('id', $item)->where('cart_id', $cart->id)->delete();

        return redirect()->route('cart.index')->with('toast', [
            'type' => 'success',
            'title' => 'حذف شد',
            'message' => 'آیتم از سبد خرید حذف شد.',
        ]);
    }

    private function findCart(Request $request): ?Cart
    {
        if (auth()->check()) {
            return Cart::query()
                ->where('user_id', auth()->id())
                ->where('status', 'active')
                ->latest('id')
                ->first();
        }

        $guestToken = $this->getGuestCartToken($request);
        if (! $guestToken) {
            return null;
        }

        return Cart::query()
            ->where('session_id', $guestToken)
            ->where('status', 'active')
            ->latest('id')
            ->first();
    }

    private function getOrCreateCart(Request $request, ?string $guestToken = null): Cart
    {
        if (auth()->check()) {
            return Cart::query()->firstOrCreate([
                'user_id' => auth()->id(),
                'status' => 'active',
            ], [
                'session_id' => null,
                'currency' => $this->commerceCurrency(),
                'meta' => [],
            ]);
        }

        $guestToken = $guestToken ?: $this->getOrCreateGuestCartToken($request);

        return Cart::query()->firstOrCreate([
            'session_id' => $guestToken,
            'status' => 'active',
        ], [
            'user_id' => null,
            'currency' => $this->commerceCurrency(),
            'meta' => [],
        ]);
    }

    private function getGuestCartToken(Request $request): ?string
    {
        $token = $request->cookie('cart_token');
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $sessionToken = $request->session()->get('cart_token');
        if (is_string($sessionToken) && $sessionToken !== '') {
            return $sessionToken;
        }

        $sessionId = $request->session()->getId();
        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        return null;
    }

    private function getOrCreateGuestCartToken(Request $request): ?string
    {
        $existing = $request->cookie('cart_token');
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $sessionToken = $request->session()->get('cart_token');
        if (is_string($sessionToken) && $sessionToken !== '') {
            return $sessionToken;
        }

        $sessionId = $request->session()->getId();
        if (is_string($sessionId) && $sessionId !== '') {
            return $sessionId;
        }

        return bin2hex(random_bytes(16));
    }

    private function guestCartCookie(Request $request, string $token): Cookie
    {
        $secure = config('session.secure');
        if ($secure === null) {
            $secure = $request->isSecure();
        }

        return Cookie::create('cart_token')
            ->withValue($token)
            ->withPath('/')
            ->withSecure((bool) $secure)
            ->withHttpOnly(true)
            ->withSameSite('lax');
    }
}
