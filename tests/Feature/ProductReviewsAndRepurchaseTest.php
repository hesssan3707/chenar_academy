<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\ProductReview;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewsAndRepurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchased_user_can_submit_review_and_it_is_visible_when_public(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'note-review-test',
            'status' => 'published',
            'base_price' => 10000,
            'currency' => 'IRR',
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
            ->post(route('products.reviews.store', $product->slug), [
                'rating' => 4,
                'body' => 'خیلی خوب بود',
            ])
            ->assertRedirect(route('products.show', $product->slug));

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'خیلی خوب بود',
        ]);

        $this->actingAs($user)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('امتیاز کاربران')
            ->assertSee('نظرات کاربران')
            ->assertSee('خیلی خوب بود')
            ->assertDontSee('ثبت نظر و امتیاز');
    }

    public function test_user_without_purchase_cannot_submit_review(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'video-review-test',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->post(route('products.reviews.store', $product->slug), [
                'rating' => 5,
                'body' => 'عالی',
            ])
            ->assertForbidden();
    }

    public function test_reviews_and_ratings_visibility_can_be_disabled_by_admin_settings(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.reviews.public', 'group' => 'commerce'],
            ['value' => false]
        );

        Setting::query()->updateOrCreate(
            ['key' => 'commerce.ratings.public', 'group' => 'commerce'],
            ['value' => false]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه مخفی',
            'slug' => 'note-hidden-review',
            'status' => 'published',
            'base_price' => 30000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'body' => 'نظر مخفی',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('امتیاز کاربران')
            ->assertDontSee('نظرات کاربران')
            ->assertDontSee('نظر مخفی');
    }

    public function test_user_can_submit_review_when_feature_is_disabled_but_it_is_not_publicly_visible(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.reviews.public', 'group' => 'commerce'],
            ['value' => false]
        );

        Setting::query()->updateOrCreate(
            ['key' => 'commerce.ratings.public', 'group' => 'commerce'],
            ['value' => false]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو غیرعمومی',
            'slug' => 'video-private-review',
            'status' => 'published',
            'base_price' => 25000,
            'currency' => 'IRR',
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
            ->post(route('products.reviews.store', $product->slug), [
                'rating' => 3,
                'body' => 'این نظر نباید عمومی باشد',
                'redirect_to' => route('products.show', $product->slug),
            ])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHas('toast');

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 3,
            'body' => 'این نظر نباید عمومی باشد',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('امتیاز کاربران')
            ->assertDontSee('نظرات کاربران')
            ->assertDontSee('این نظر نباید عمومی باشد');
    }

    public function test_reviews_placeholder_is_shown_when_no_reviews_exist_and_reviews_are_public(): void
    {
        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو بدون نظر',
            'slug' => 'video-no-reviews',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('نظرات کاربران')
            ->assertSee('اولین نفری باشید که این ویدیو را بررسی می‌کند.');
    }

    public function test_review_requires_admin_approval_when_setting_is_enabled(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.reviews.require_approval', 'group' => 'commerce'],
            ['value' => true]
        );

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه با تایید ادمین',
            'slug' => 'note-review-approval-required',
            'status' => 'published',
            'base_price' => 10000,
            'currency' => 'IRR',
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
            ->post(route('products.reviews.store', $product->slug), [
                'rating' => 4,
                'body' => 'نیاز به تایید دارد',
                'redirect_to' => route('products.show', $product->slug),
            ])
            ->assertRedirect(route('products.show', $product->slug));

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'نیاز به تایید دارد',
            'status' => 'pending',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('نیاز به تایید دارد');

        $this->actingAs($user)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('نیاز به تایید دارد')
            ->assertSee('در انتظار بررسی');
    }

    public function test_admin_can_approve_or_reject_pending_reviews(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.reviews.require_approval', 'group' => 'commerce'],
            ['value' => true]
        );

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو با نظر تاییدی',
            'slug' => 'video-review-approval-required',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $review = ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'body' => 'نظر در انتظار تایید',
            'status' => 'pending',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('نظر در انتظار تایید')
            ->assertSee('در انتظار');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.reviews.approve', $review->id))
            ->assertRedirect(route('admin.reviews.index'));

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'approved',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('نظر در انتظار تایید');

        $this->actingAs($admin, 'admin')
            ->post(route('admin.reviews.reject', $review->id))
            ->assertRedirect(route('admin.reviews.index'));

        $this->assertDatabaseHas('product_reviews', [
            'id' => $review->id,
            'status' => 'rejected',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertDontSee('نظر در انتظار تایید');
    }

    public function test_admin_reviews_list_shows_new_label_when_approval_is_not_required(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.reviews.require_approval', 'group' => 'commerce'],
            ['value' => false]
        );

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه با نظر فوری',
            'slug' => 'note-review-no-approval',
            'status' => 'published',
            'base_price' => 10000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        ProductReview::query()->create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'نظر جدید',
            'status' => 'approved',
        ]);

        $this->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('نظر جدید');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('نظر جدید')
            ->assertSee('جدید');
    }

    public function test_user_can_submit_review_from_library_and_form_is_hidden_after_submission(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه کتابخانه',
            'slug' => 'note-library-review',
            'status' => 'published',
            'base_price' => 20000,
            'currency' => 'IRR',
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
            ->assertSee('ثبت نظر و امتیاز');

        $this->actingAs($user)
            ->post(route('products.reviews.store', $product->slug), [
                'rating' => 5,
                'body' => 'از داخل کتابخانه',
                'redirect_to' => route('panel.library.show', $product->slug),
            ])
            ->assertRedirect(route('panel.library.show', $product->slug))
            ->assertSessionHas('toast');

        $this->actingAs($user)
            ->get(route('panel.library.show', $product->slug))
            ->assertOk()
            ->assertDontSee('ثبت نظر و امتیاز');
    }

    public function test_purchased_label_shown_and_user_cannot_add_purchased_product_to_cart(): void
    {
        $user = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه خریداری‌شده',
            'slug' => 'purchased-note',
            'status' => 'published',
            'base_price' => 40000,
            'currency' => 'IRR',
            'published_at' => now(),
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
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('خریداری شده');

        $this->actingAs($user)
            ->post(route('cart.items.store'), [
                'product_id' => $product->id,
            ])
            ->assertRedirect(route('products.show', $product->slug))
            ->assertSessionHas('toast');

        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($user)
            ->get(route('products.show', $product->slug))
            ->assertOk()
            ->assertSee('مشاهده در کتابخانه')
            ->assertDontSee('افزودن به سبد');
    }
}
