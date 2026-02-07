<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostBlock;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_loads(): void
    {
        $this->get('/about')->assertOk();
    }

    public function test_about_page_loads_content_from_database_when_present(): void
    {
        Setting::query()->create([
            'key' => 'page.about',
            'group' => 'pages',
            'value' => [
                'title' => 'درباره ما',
                'subtitle' => 'معرفی کوتاه',
                'body' => 'متن درباره ما',
            ],
        ]);

        $this->get('/about')
            ->assertOk()
            ->assertSee('درباره ما')
            ->assertSee('معرفی کوتاه')
            ->assertSee('متن درباره ما');
    }

    public function test_contact_page_loads(): void
    {
        $this->get('/contact')->assertOk();
    }

    public function test_blog_index_page_loads_posts_from_database(): void
    {
        Post::query()->create([
            'author_user_id' => null,
            'title' => 'مقاله تست',
            'slug' => 'test-post',
            'excerpt' => 'خلاصه مقاله تست',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_media_id' => null,
            'meta' => [],
        ]);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('مقاله تست');
    }

    public function test_blog_show_page_loads_post_content_blocks(): void
    {
        $post = Post::query()->create([
            'author_user_id' => null,
            'title' => 'مقاله تست',
            'slug' => 'test-post',
            'excerpt' => 'خلاصه مقاله تست',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'cover_media_id' => null,
            'meta' => [],
        ]);

        PostBlock::query()->create([
            'post_id' => $post->id,
            'block_type' => 'text',
            'sort_order' => 0,
            'text' => 'بدنه مقاله',
            'media_id' => null,
            'meta' => [],
        ]);

        $this->get(route('blog.show', ['slug' => 'test-post']))
            ->assertOk()
            ->assertSee('مقاله تست')
            ->assertSee('بدنه مقاله');
    }
}
