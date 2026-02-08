<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\ProductPart;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelLibraryAndOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_access_cannot_view_library_product(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'note-test',
            'status' => 'published',
            'base_price' => 10000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('panel.library.show', $product->slug))
            ->assertForbidden();
    }

    public function test_user_with_access_can_view_note_parts_in_library(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه چندبخشی',
            'slug' => 'multi-part-note',
            'status' => 'published',
            'base_price' => 10000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        ProductPart::query()->create([
            'product_id' => $product->id,
            'part_type' => 'text',
            'title' => 'بخش اول',
            'sort_order' => 1,
            'media_id' => null,
            'content' => "پاراگراف اول\n\nپاراگراف دوم",
            'meta' => [],
        ]);

        ProductAccess::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('panel.library.index'))
            ->assertOk()
            ->assertSee('جزوه چندبخشی');

        $this->actingAs($user)
            ->get(route('panel.library.show', $product->slug))
            ->assertOk()
            ->assertSee('بخش اول')
            ->assertSee('پاراگراف اول')
            ->assertSee('پاراگراف دوم');
    }

    public function test_user_with_access_can_stream_video_through_panel_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('protected/video.mp4', 'dummy-video-bytes');

        $user = User::factory()->create();

        $media = Media::query()->create([
            'uploaded_by_user_id' => null,
            'disk' => 'local',
            'path' => 'protected/video.mp4',
            'original_name' => 'video.mp4',
            'mime_type' => 'video/mp4',
            'size' => 16,
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'video-test',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        Video::query()->create([
            'product_id' => $product->id,
            'media_id' => $media->id,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        ProductAccess::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('panel.library.show', $product->slug))
            ->assertOk()
            ->assertSee(route('panel.library.video.stream', ['product' => $product->slug]), false);

        $response = $this->actingAs($user)
            ->get(route('panel.library.video.stream', ['product' => $product->slug]))
            ->assertOk();

        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);

        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)
            ->get(route('panel.library.video.stream', ['product' => $product->slug]))
            ->assertForbidden();
    }

    public function test_user_can_only_view_own_orders_in_panel(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $myOrder = Order::query()->create([
            'order_number' => 'ORD-1001',
            'user_id' => $user->id,
            'status' => 'paid',
            'currency' => 'IRR',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'payable_amount' => 10000,
            'placed_at' => now(),
            'paid_at' => now(),
            'cancelled_at' => null,
            'meta' => [],
        ]);

        $otherOrder = Order::query()->create([
            'order_number' => 'ORD-2001',
            'user_id' => $otherUser->id,
            'status' => 'paid',
            'currency' => 'IRR',
            'subtotal_amount' => 10000,
            'discount_amount' => 0,
            'total_amount' => 10000,
            'payable_amount' => 10000,
            'placed_at' => now(),
            'paid_at' => now(),
            'cancelled_at' => null,
            'meta' => [],
        ]);

        OrderItem::query()->create([
            'order_id' => $myOrder->id,
            'product_id' => null,
            'product_type' => 'note',
            'product_title' => 'جزوه تست',
            'quantity' => 1,
            'unit_price' => 10000,
            'total_price' => 10000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('panel.orders.index'))
            ->assertOk()
            ->assertSee('ORD-1001')
            ->assertDontSee('ORD-2001');

        $this->actingAs($user)
            ->get(route('panel.orders.show', $myOrder->id))
            ->assertOk()
            ->assertSee('ORD-1001')
            ->assertSee('جزوه تست');

        $this->actingAs($user)
            ->get(route('panel.orders.show', $otherOrder->id))
            ->assertNotFound();
    }
}
