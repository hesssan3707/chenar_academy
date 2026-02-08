<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_tax_percent_setting_adds_tax_to_payable_amount(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.tax_percent', 'group' => 'commerce'],
            ['value' => 9]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'checkout-note-tax',
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
            ->assertSee('مالیات')
            ->assertSee('9٪')
            ->assertSee(number_format(9000), false)
            ->assertSee(number_format(109000), false);
    }

    public function test_tax_is_calculated_after_discount(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.tax_percent', 'group' => 'commerce'],
            ['value' => 9]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'checkout-video-tax',
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
            ->assertSee(number_format(16200), false)
            ->assertSee(number_format(196200), false);
    }

    public function test_user_can_submit_card_to_card_receipt_and_order_becomes_pending_review(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'checkout-note-c2c',
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
            ->post(route('checkout.card-to-card.store'), [
                'receipt' => UploadedFile::fake()->image('receipt.jpg', 800, 600),
            ])
            ->assertRedirect();

        $order = Order::query()->where('user_id', $user->id)->latest('id')->firstOrFail();
        $this->assertSame('pending_review', (string) $order->status);
        $this->assertSame('card_to_card', (string) (($order->meta ?? [])['payment_method'] ?? ''));

        $payment = Payment::query()->where('order_id', $order->id)->latest('id')->firstOrFail();
        $this->assertSame('card_to_card', (string) $payment->gateway);
        $this->assertSame('pending_review', (string) $payment->status);

        $receiptMediaId = (int) (($payment->meta ?? [])['receipt_media_id'] ?? 0);
        $this->assertNotSame(0, $receiptMediaId);
        $receipt = \App\Models\Media::query()->findOrFail($receiptMediaId);
        $this->assertSame('local', (string) $receipt->disk);
        Storage::disk('local')->assertExists($receipt->path);
    }

    public function test_admin_can_approve_card_to_card_order_and_grant_access(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $buyer = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه پرداختی',
            'slug' => 'c2c-paid-note',
            'status' => 'published',
            'base_price' => 120000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-C2C-1001',
            'user_id' => $buyer->id,
            'status' => 'pending_review',
            'currency' => 'IRR',
            'subtotal_amount' => 120000,
            'discount_amount' => 0,
            'total_amount' => 120000,
            'payable_amount' => 120000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [
                'payment_method' => 'card_to_card',
            ],
        ]);

        \App\Models\OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_type' => 'note',
            'product_title' => $product->title,
            'quantity' => 1,
            'unit_price' => 120000,
            'total_price' => 120000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $receiptPath = Storage::disk('local')->putFile('receipts', UploadedFile::fake()->image('receipt.jpg', 800, 600));
        $receipt = \App\Models\Media::query()->create([
            'uploaded_by_user_id' => $buyer->id,
            'disk' => 'local',
            'path' => $receiptPath,
            'original_name' => 'receipt.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 123,
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'card_to_card',
            'status' => 'pending_review',
            'amount' => 120000,
            'currency' => 'IRR',
            'authority' => null,
            'reference_id' => null,
            'paid_at' => null,
            'meta' => [
                'receipt_media_id' => $receipt->id,
            ],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.orders.card-to-card.approve', $order->id))
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('paid', (string) $order->status);

        $this->assertDatabaseHas('product_accesses', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
        ]);
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
