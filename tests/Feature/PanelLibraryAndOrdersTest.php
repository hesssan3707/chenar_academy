<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Media;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\ProductPart;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PanelLibraryAndOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_page_shows_chapters_and_locked_state_before_purchase(): void
    {
        $courseProduct = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره تست',
            'slug' => 'course-test',
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

        Course::query()->create([
            'product_id' => $courseProduct->id,
            'body' => null,
            'level' => null,
            'total_duration_seconds' => null,
            'meta' => [],
        ]);

        $section = CourseSection::query()->create([
            'course_product_id' => $courseProduct->id,
            'title' => 'فصل اول',
            'sort_order' => 0,
        ]);

        CourseLesson::query()->create([
            'course_section_id' => $section->id,
            'title' => 'درس رایگان',
            'sort_order' => 0,
            'lesson_type' => 'video',
            'media_id' => null,
            'content' => null,
            'is_preview' => true,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        CourseLesson::query()->create([
            'course_section_id' => $section->id,
            'title' => 'درس پولی',
            'sort_order' => 1,
            'lesson_type' => 'video',
            'media_id' => null,
            'content' => null,
            'is_preview' => false,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $this->get(route('courses.show', $courseProduct->slug))
            ->assertOk()
            ->assertSee('سرفصل‌های دوره')
            ->assertSee('فصل اول')
            ->assertSee('درس رایگان')
            ->assertSee('پیش‌نمایش')
            ->assertSee('درس پولی')
            ->assertSee('قفل');
    }

    public function test_purchased_course_page_links_to_library_instead_of_add_to_cart(): void
    {
        $user = User::factory()->create();

        $courseProduct = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره خریداری‌شده',
            'slug' => 'course-purchased',
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

        Course::query()->create([
            'product_id' => $courseProduct->id,
            'body' => null,
            'level' => null,
            'total_duration_seconds' => null,
            'meta' => [],
        ]);

        ProductAccess::query()->create([
            'user_id' => $user->id,
            'product_id' => $courseProduct->id,
            'order_item_id' => null,
            'granted_at' => now(),
            'expires_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->get(route('courses.show', $courseProduct->slug))
            ->assertOk()
            ->assertSee('مشاهده در کتابخانه')
            ->assertDontSee('افزودن به سبد');
    }

    public function test_panel_navigation_and_dashboard_link_to_orders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('panel.dashboard'))
            ->assertRedirect(route('panel.library.index'));

        $this->actingAs($user)
            ->get(route('panel.library.index'))
            ->assertOk()
            ->assertSee(route('panel.orders.index'), false);

        $this->actingAs($user)
            ->get(route('panel.orders.index'))
            ->assertOk()
            ->assertSee('سفارش');
    }

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

    public function test_panel_forces_password_setup_modal_for_users_without_password(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user)
            ->get(route('panel.library.index'))
            ->assertOk()
            ->assertSee('name="force-password-setup"', false)
            ->assertSee('password-setup-modal', false);
    }

    public function test_user_without_password_can_set_password_without_current_password(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user)
            ->from(route('panel.profile'))
            ->put(route('panel.profile.password.update'), [
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect(route('panel.profile'));

        $user->refresh();

        $this->assertNotNull($user->password);
        $this->assertTrue(Hash::check('newpass123', (string) $user->password));
    }

    public function test_password_update_requires_letters_and_numbers(): void
    {
        $user = User::factory()->create(['password' => null]);

        $this->actingAs($user)
            ->from(route('panel.profile'))
            ->put(route('panel.profile.password.update'), [
                'password' => 'abcdef',
                'password_confirmation' => 'abcdef',
            ])
            ->assertRedirect(route('panel.profile'))
            ->assertSessionHasErrors(['password']);
    }

    public function test_user_with_password_must_provide_current_password_to_change_it(): void
    {
        $user = User::factory()->create([
            'password' => 'oldpass123',
        ]);

        $this->actingAs($user)
            ->from(route('panel.profile'))
            ->put(route('panel.profile.password.update'), [
                'password' => 'newpass123',
                'password_confirmation' => 'newpass123',
            ])
            ->assertRedirect(route('panel.profile'))
            ->assertSessionHasErrors(['current_password']);
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
            ->assertDontSee('جزوه‌ها')
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
            ->assertDontSee('ویدیوها و دوره‌ها')
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

    public function test_user_with_access_can_download_booklet_pdf_from_library(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('protected/booklets/booklet.pdf', 'dummy-pdf-bytes');

        $user = User::factory()->create();

        $media = Media::query()->create([
            'uploaded_by_user_id' => null,
            'disk' => 'local',
            'path' => 'protected/booklets/booklet.pdf',
            'original_name' => 'booklet.pdf',
            'mime_type' => 'application/pdf',
            'size' => 15,
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه PDF',
            'slug' => 'note-pdf',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $part = ProductPart::query()->create([
            'product_id' => $product->id,
            'part_type' => 'file',
            'title' => 'فایل جزوه',
            'sort_order' => 0,
            'media_id' => $media->id,
            'content' => null,
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

        $response = $this->actingAs($user)
            ->get(route('panel.library.parts.stream', ['product' => $product->slug, 'part' => $part->id]))
            ->assertOk();

        $contentDisposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('booklet.pdf', $contentDisposition);
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

    public function test_product_details_does_not_show_purchase_required_review_message(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه بدون خرید',
            'slug' => 'note-without-purchase',
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

        $this->actingAs($user)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('برای ثبت نظر ابتدا محصول را خریداری کنید.')
            ->assertDontSee('ثبت نظر و امتیاز');
    }
}
