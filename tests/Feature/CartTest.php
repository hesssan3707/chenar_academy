<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
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
            ->assertSee('جزوه تست')
            ->assertDontSee('تعداد')
            ->assertDontSee('جمع این آیتم');
    }

    public function test_adding_same_product_twice_does_not_increment_quantity(): void
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
        $this->assertSame(1, (int) $cartItem->quantity);
        $this->assertSame(70000, (int) $cartItem->unit_price);
    }

    public function test_list_pages_render_add_to_cart_forms(): void
    {
        $note = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'test-note-list',
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

        $course = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره تست',
            'slug' => 'test-course-list',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 120000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('action="'.route('cart.items.store').'"', false)
            ->assertSee('name="product_id" value="'.$note->id.'"', false)
            ->assertSee('name="product_id" value="'.$course->id.'"', false);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('action="'.route('cart.items.store').'"', false)
            ->assertSee('name="product_id" value="'.$course->id.'"', false);

        $category = Category::query()->create([
            'type' => 'note',
            'title' => 'دسته تست',
            'slug' => 'test-note-category',
            'is_active' => true,
            'sort_order' => 0,
            'parent_id' => null,
        ]);

        $note->categories()->sync([$category->id]);

        $this->get(route('booklets.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('action="'.route('cart.items.store').'"', false)
            ->assertSee('name="product_id" value="'.$note->id.'"', false);
    }
}
