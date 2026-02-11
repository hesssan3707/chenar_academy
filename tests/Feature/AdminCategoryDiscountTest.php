<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_apply_discount_to_all_products_in_category(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $category = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'ریاضی ۱',
            'slug' => 'math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $includedA = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه A',
            'slug' => 'note-a',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 200000,
            'sale_price' => 180000,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $includedB = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه B',
            'slug' => 'note-b',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 200000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $excluded = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه خارج از دسته',
            'slug' => 'note-excluded',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 200000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $includedA->categories()->attach($category->id);
        $includedB->categories()->attach($category->id);

        $this->actingAs($admin, 'admin')->post(route('admin.discounts.category.apply'), [
            'category_id' => $category->id,
            'discount_type' => 'percent',
            'discount_value' => 20,
        ])->assertRedirect(route('admin.discounts.category'));

        $this->assertDatabaseHas('products', [
            'id' => $includedA->id,
            'sale_price' => null,
            'discount_type' => 'percent',
            'discount_value' => 20,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $includedB->id,
            'sale_price' => null,
            'discount_type' => 'percent',
            'discount_value' => 20,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $excluded->id,
            'discount_type' => null,
            'discount_value' => null,
        ]);
    }
}
