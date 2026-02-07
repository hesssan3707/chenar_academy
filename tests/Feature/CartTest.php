<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_product_to_cart_and_see_it(): void
    {
        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'test-note',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 100000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseCount('cart_items', 1);

        $cart = Cart::query()->firstOrFail();
        $this->assertNotNull($cart->session_id);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
        ]);

        $this->withSession(['cart_token' => $cart->session_id])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('جزوه تست');
    }

    public function test_adding_same_product_twice_increments_quantity(): void
    {
        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'test-video',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 90000,
            'sale_price' => 70000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
        ])->assertRedirect(route('cart.index'));

        $cart = Cart::query()->firstOrFail();
        $this->assertNotNull($cart->session_id);

        $this->withSession(['cart_token' => $cart->session_id])
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
            ])->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('cart_items', 1);

        $cartItem = CartItem::query()->firstOrFail();
        $this->assertSame(2, (int) $cartItem->quantity);
        $this->assertSame(70000, (int) $cartItem->unit_price);
    }
}
