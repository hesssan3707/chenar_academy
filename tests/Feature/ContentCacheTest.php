<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_index_cache_is_cleared_on_post_change(): void
    {
        Cache::flush();

        Post::query()->create([
            'author_user_id' => null,
            'title' => 'پست اول',
            'slug' => 'post-1',
            'excerpt' => null,
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_media_id' => null,
            'meta' => [],
        ]);

        $cacheKey = 'content_cache.posts.index.v1.limit30';

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('پست اول');

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertTrue(Cache::has('content_cache_keys.posts'));

        Post::query()->create([
            'author_user_id' => null,
            'title' => 'پست جدید',
            'slug' => 'post-new',
            'excerpt' => null,
            'status' => 'published',
            'published_at' => now(),
            'cover_media_id' => null,
            'meta' => [],
        ]);

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('content_cache_keys.posts'));

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('پست جدید');

        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_products_index_cache_is_cleared_on_product_change(): void
    {
        Cache::flush();

        Product::query()->create([
            'type' => 'note',
            'title' => 'محصول اول',
            'slug' => 'product-1',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 100000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now()->subDay(),
            'meta' => [],
        ]);

        $cacheKey = 'content_cache.products.index.v3.'.sha1(json_encode([
            'type' => null,
            'category' => null,
        ], JSON_THROW_ON_ERROR));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('محصول اول');

        $this->assertTrue(Cache::has($cacheKey));
        $this->assertTrue(Cache::has('content_cache_keys.products'));

        Product::query()->create([
            'type' => 'video',
            'title' => 'محصول جدید',
            'slug' => 'product-new',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 70000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->assertFalse(Cache::has($cacheKey));
        $this->assertFalse(Cache::has('content_cache_keys.products'));

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('محصول جدید');

        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_header_and_footer_render_social_icons_when_no_social_links_exist(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('aria-label="اینستاگرام"', false)
            ->assertSee('aria-label="تلگرام"', false)
            ->assertSee('aria-label="یوتیوب"', false);
    }
}
