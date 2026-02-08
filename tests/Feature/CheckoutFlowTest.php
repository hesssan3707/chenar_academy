<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_shows_coupon_input_and_invoice(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'checkout-note',
            'status' => 'published',
            'base_price' => 100000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('کد تخفیف')
            ->assertSee('فاکتور نهایی')
            ->assertSee('پرداخت');
    }

    public function test_coupon_can_be_applied_and_reduces_payable_amount(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'checkout-video',
            'status' => 'published',
            'base_price' => 200000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 200000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        Coupon::query()->create([
            'code' => 'OFF10',
            'discount_type' => 'percent',
            'discount_value' => 10,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'usage_limit' => null,
            'per_user_limit' => null,
            'used_count' => 0,
            'is_active' => true,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->post(route('checkout.coupon.apply'), [
                'code' => 'OFF10',
            ])
            ->assertRedirect(route('checkout.index'));

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee(number_format(20000), false)
            ->assertSee(number_format(180000), false);
    }

    public function test_mock_gateway_success_marks_order_paid_and_grants_access(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه پرداختی',
            'slug' => 'paid-note',
            'status' => 'published',
            'base_price' => 120000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 120000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $response = $this->actingAs($user)->post(route('checkout.pay'));
        $response->assertRedirect();

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('mock', $payment->gateway);
        $this->assertSame('initiated', $payment->status);

        $this->actingAs($user)
            ->get(route('checkout.mock-gateway.show', $payment->id))
            ->assertOk()
            ->assertSee('درگاه پرداخت آزمایشی');

        $this->actingAs($user)
            ->post(route('checkout.mock-gateway.return', $payment->id), [
                'result' => 'success',
            ])
            ->assertRedirect(route('panel.library.index'));

        $payment->refresh();
        $order = Order::query()->firstOrFail();

        $this->assertSame('paid', $payment->status);
        $this->assertSame('paid', $order->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($order->paid_at);

        $this->assertDatabaseHas('product_accesses', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'status' => 'checked_out',
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_mock_gateway_fail_does_not_grant_access(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو پرداختی',
            'slug' => 'paid-video',
            'status' => 'published',
            'base_price' => 80000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 80000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)->post(route('checkout.pay'))->assertRedirect();

        $payment = Payment::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('checkout.mock-gateway.return', $payment->id), [
                'result' => 'fail',
            ])
            ->assertRedirect(route('checkout.index'));

        $payment->refresh();
        $this->assertSame('failed', $payment->status);

        $this->assertDatabaseCount('product_accesses', 0);

        $this->assertDatabaseHas('carts', [
            'id' => $cart->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_access_expiration_setting_sets_expires_at_on_purchase(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.access_expiration_days', 'group' => 'commerce'],
            ['value' => 365]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه با انقضا',
            'slug' => 'note-with-expiration',
            'status' => 'published',
            'base_price' => 120000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 120000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)->post(route('checkout.pay'))->assertRedirect();

        $payment = Payment::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('checkout.mock-gateway.return', $payment->id), [
                'result' => 'success',
            ])
            ->assertRedirect(route('panel.library.index'));

        $access = \App\Models\ProductAccess::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->firstOrFail();

        $this->assertNotNull($access->expires_at);
        $this->assertNotNull($access->granted_at);
        $this->assertSame(365, $access->granted_at->diffInDays($access->expires_at));
    }
}
