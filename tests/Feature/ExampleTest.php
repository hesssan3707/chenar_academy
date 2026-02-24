<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_homepage_shows_best_sellers_row_and_limits_to_ten_items(): void
    {
        $user = User::create([
            'name' => 'Buyer',
            'phone' => '09120000001',
            'email' => 'buyer@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $products = collect(range(1, 12))->map(function (int $i) {
            return Product::create([
                'type' => 'note',
                'title' => "محصول {$i}",
                'slug' => "product-{$i}",
                'excerpt' => null,
                'description' => null,
                'thumbnail_media_id' => null,
                'status' => 'published',
                'base_price' => 10000,
                'sale_price' => null,
                'currency' => 'IRR',
                'published_at' => now()->subMinutes($i),
                'meta' => [],
            ]);
        });

        $order = Order::create([
            'order_number' => 'ORD-0001',
            'user_id' => $user->id,
            'status' => 'paid',
            'currency' => 'IRR',
            'subtotal_amount' => 120000,
            'discount_amount' => 0,
            'total_amount' => 120000,
            'payable_amount' => 120000,
            'placed_at' => now()->subMinute(),
            'paid_at' => now(),
            'cancelled_at' => null,
            'meta' => [],
        ]);

        foreach ($products as $index => $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_type' => $product->type,
                'product_title' => $product->title,
                'quantity' => $index + 1,
                'unit_price' => 10000,
                'total_price' => 10000 * ($index + 1),
                'currency' => 'IRR',
                'meta' => [],
            ]);
        }

        $response = $this->get(route('home'));
        $response->assertOk()->assertSee('پرفروش‌ها')->assertSee('جدیدترین‌ها')->assertDontSee('ادامه یادگیری');

        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertSame(20, substr_count($content, 'card-product--home'));
    }

    public function test_authenticated_homepage_shows_continue_learning_row(): void
    {
        $user = User::create([
            'name' => 'Student',
            'phone' => '09120000002',
            'email' => 'student@example.com',
            'password' => 'password123',
            'is_active' => true,
        ]);

        $product = Product::create([
            'type' => 'note',
            'title' => 'محصول خریداری شده',
            'slug' => 'purchased-product',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 10000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        ProductAccess::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('ادامه یادگیری')
            ->assertSee('محصول خریداری شده')
            ->assertDontSee('پرفروش‌ها');
    }
}
