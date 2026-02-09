<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_booklets_page_shows_only_categories_until_category_selected(): void
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه آزاد اسلامی',
            'slug' => 'iau',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'ریاضی ۱',
            'slug' => 'iau-math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه ریاضی ۱',
            'slug' => 'note-math-1',
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
        $product->categories()->attach($category->id);

        $this->get(route('products.index', ['type' => 'note']))
            ->assertOk()
            ->assertSee($category->title)
            ->assertSee(route('products.index', ['type' => 'note', 'category' => $category->slug]), false)
            ->assertDontSee($product->title);

        $this->get(route('products.index', ['type' => 'note', 'category' => $category->slug]))
            ->assertOk()
            ->assertSee($product->title);
    }

    public function test_videos_page_shows_only_categories_until_category_selected(): void
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه آزاد اسلامی',
            'slug' => 'iau',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => $institution->id,
            'title' => 'فصل ۱',
            'slug' => 'iau-ch1',
            'icon_key' => 'video',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو فصل ۱',
            'slug' => 'video-ch1',
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
        $product->categories()->attach($category->id);

        $this->get(route('products.index', ['type' => 'video']))
            ->assertOk()
            ->assertSee($category->title)
            ->assertSee(route('products.index', ['type' => 'video', 'category' => $category->slug]), false)
            ->assertDontSee($product->title);

        $this->get(route('products.index', ['type' => 'video', 'category' => $category->slug]))
            ->assertOk()
            ->assertSee($product->title);
    }

    public function test_videos_page_shows_courses_alongside_videos_in_category_flow(): void
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه آزاد اسلامی',
            'slug' => 'iau',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => $institution->id,
            'title' => 'فصل ۱',
            'slug' => 'iau-ch1',
            'icon_key' => 'video',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $video = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو فصل ۱',
            'slug' => 'video-ch1',
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
        $video->categories()->attach($category->id);

        $course = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره فصل ۱',
            'slug' => 'course-ch1',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 200000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);
        $course->categories()->attach($category->id);

        $this->get(route('products.index', ['type' => 'video']))
            ->assertOk()
            ->assertDontSee($video->title)
            ->assertDontSee($course->title);

        $this->get(route('products.index', ['type' => 'video', 'category' => $category->slug]))
            ->assertOk()
            ->assertSee($video->title)
            ->assertSee($course->title);
    }

    public function test_products_index_filters_by_category(): void
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه آزاد اسلامی',
            'slug' => 'iau',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'ریاضی ۱',
            'slug' => 'iau-math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $included = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه ریاضی ۱',
            'slug' => 'note-math-1',
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

        $excluded = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه فیزیک',
            'slug' => 'note-physics',
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

        $included->categories()->attach($category->id);

        $this->get(route('products.index', ['type' => 'note', 'category' => $category->slug]))
            ->assertOk()
            ->assertSee($included->title)
            ->assertDontSee($excluded->title);
    }
}
