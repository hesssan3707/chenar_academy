<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCourseUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_course_with_cover_and_videos_and_duration_is_calculated(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Azad University',
            'slug' => 'azad',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => null,
            'title' => 'Math Courses',
            'slug' => 'math-courses',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $callCount = 0;
        Process::fake(function () use (&$callCount) {
            $callCount++;

            if ($callCount === 1) {
                return Process::result(output: "60.0\n");
            }

            return Process::result(output: "120.0\n");
        });

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.courses.store'), [
            'title' => 'My Course',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'discount_type' => null,
            'discount_value' => null,
            'published_at' => null,
            'description' => "Line 1\n\nLine 2",
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 600),
            'lessons' => [
                'new_0' => [
                    'title' => 'Free Video',
                    'is_preview' => '1',
                    'sort_order' => 0,
                    'file' => UploadedFile::fake()->create('free.mp4', 2400, 'video/mp4'),
                ],
                'new_1' => [
                    'title' => 'Paid Video',
                    'is_preview' => '0',
                    'sort_order' => 1,
                    'file' => UploadedFile::fake()->create('paid.mp4', 3600, 'video/mp4'),
                ],
            ],
        ]);

        $response->assertRedirect();

        $product = Product::query()->where('type', 'course')->where('title', 'My Course')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->thumbnail_media_id);
        $this->assertSame($institution->id, (int) $product->institution_category_id);

        $this->assertDatabaseHas('product_categories', [
            'product_id' => (int) $product->id,
            'category_id' => (int) $category->id,
        ]);

        $course = Course::query()->find((int) $product->id);
        $this->assertNotNull($course);
        $this->assertSame(2, (int) $course->total_videos_count);
        $this->assertSame(180, (int) $course->total_duration_seconds);

        $this->assertDatabaseHas('course_lessons', [
            'title' => 'Free Video',
            'is_preview' => 1,
        ]);

        $this->assertDatabaseHas('course_lessons', [
            'title' => 'Paid Video',
            'is_preview' => 0,
        ]);

        $freeLesson = CourseLesson::query()->where('title', 'Free Video')->first();
        $this->assertNotNull($freeLesson);
        $this->assertNotNull($freeLesson->media_id);
    }
}
