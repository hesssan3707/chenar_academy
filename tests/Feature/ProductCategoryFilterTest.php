<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryFilterTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_products_index_filters_by_institution(): void
    {
        $iau = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه آزاد اسلامی',
            'slug' => 'iau',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $pnu = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'دانشگاه پیام نور',
            'slug' => 'pnu',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $iauMath = Category::query()->create([
            'type' => 'note',
            'parent_id' => $iau->id,
            'title' => 'ریاضی ۱',
            'slug' => 'iau-math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $pnuMath = Category::query()->create([
            'type' => 'note',
            'parent_id' => $pnu->id,
            'title' => 'ریاضی ۱',
            'slug' => 'pnu-math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $iauProduct = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه ریاضی آزاد',
            'slug' => 'note-iau',
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

        $pnuProduct = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه ریاضی پیام نور',
            'slug' => 'note-pnu',
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

        $iauProduct->categories()->attach($iauMath->id);
        $pnuProduct->categories()->attach($pnuMath->id);

        $this->get(route('products.index', ['type' => 'note', 'institution' => $iau->slug]))
            ->assertOk()
            ->assertSee($iauProduct->title)
            ->assertDontSee($pnuProduct->title);
    }
}
