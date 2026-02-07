<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Post;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederAndHomeContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seeder_includes_post_and_ticket_categories(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertDatabaseHas('categories', ['type' => 'post', 'slug' => 'news']);
        $this->assertDatabaseHas('categories', ['type' => 'post', 'slug' => 'learning']);
        $this->assertDatabaseHas('categories', ['type' => 'ticket', 'slug' => 'technical']);

        $this->assertGreaterThanOrEqual(3, Category::query()->where('type', 'post')->count());
        $this->assertGreaterThanOrEqual(3, Category::query()->where('type', 'ticket')->count());
    }

    public function test_catalog_seeder_includes_institutions_and_note_video_categories_with_icons(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertDatabaseHas('categories', ['type' => 'institution', 'slug' => 'iau']);
        $this->assertDatabaseHas('categories', ['type' => 'institution', 'slug' => 'pnu']);

        $iau = Category::query()->where('type', 'institution')->where('slug', 'iau')->first();
        $this->assertNotNull($iau);

        $this->assertDatabaseHas('categories', [
            'type' => 'note',
            'slug' => 'iau-math-1',
            'parent_id' => $iau->id,
            'icon_key' => 'math',
        ]);

        $this->assertGreaterThanOrEqual(2, Category::query()->where('type', 'institution')->count());
        $this->assertGreaterThanOrEqual(1, Category::query()->where('type', 'note')->count());
        $this->assertGreaterThanOrEqual(1, Category::query()->where('type', 'video')->count());
    }

    public function test_homepage_loads_banner_and_posts_from_database(): void
    {
        Banner::query()->create([
            'title' => 'بنر صفحه اصلی',
            'position' => 'home',
            'image_media_id' => null,
            'link_url' => '/blog',
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
            'sort_order' => 0,
            'meta' => [],
        ]);

        Post::query()->create([
            'author_user_id' => null,
            'title' => 'مقاله اول',
            'slug' => 'post-1',
            'excerpt' => 'خلاصه مقاله اول',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_media_id' => null,
            'meta' => [],
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('بنر صفحه اصلی')
            ->assertSee('مقاله اول');
    }
}
