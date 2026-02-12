<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostBlock;
use App\Models\Product;
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
            ->assertSee('مقاله تست')
            ->assertDontSee('خلاصه مقاله تست');
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
            ->assertSee('بدنه مقاله')
            ->assertSee('detail-shell')
            ->assertSee('detail-grid');
    }

    public function test_product_show_pages_load_for_note_and_video(): void
    {
        $note = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تست',
            'slug' => 'test-note',
            'excerpt' => 'خلاصه جزوه',
            'description' => "پاراگراف اول\n\nپاراگراف دوم",
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 100000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $video = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو تست',
            'slug' => 'test-video',
            'excerpt' => null,
            'description' => "متن اول\n\nمتن دوم",
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 200000,
            'sale_price' => 150000,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->get(route('products.show', $note->slug))
            ->assertOk()
            ->assertSee($note->title)
            ->assertSee('پاراگراف اول')
            ->assertSee('پاراگراف دوم')
            ->assertSee('detail-shell')
            ->assertSee('detail-grid')
            ->assertDontSee('max-h-[80vh]');

        $this->get(route('products.show', $video->slug))
            ->assertOk()
            ->assertSee($video->title)
            ->assertSee('ویدیو قفل است')
            ->assertSee('امتیاز و نظرات')
            ->assertDontSee('پیش‌نمایش')
            ->assertSee('متن اول')
            ->assertSee('متن دوم')
            ->assertSee('صرفه‌جویی')
            ->assertSee('25% OFF')
            ->assertSee('detail-section')
            ->assertSee('detail-shell')
            ->assertSee('detail-grid')
            ->assertDontSee('max-h-[80vh]');
    }

    public function test_course_show_page_loads(): void
    {
        $course = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره تست',
            'slug' => 'test-course',
            'excerpt' => null,
            'description' => "توضیح اول\n\nتوضیح دوم",
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 300000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->get(route('courses.show', $course->slug))
            ->assertOk()
            ->assertSee($course->title)
            ->assertSee('توضیح اول')
            ->assertSee('توضیح دوم')
            ->assertSee('detail-shell')
            ->assertSee('detail-grid')
            ->assertDontSee('max-h-[80vh]');

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee($course->title)
            ->assertDontSee('مشاهده جزئیات');
    }
}
