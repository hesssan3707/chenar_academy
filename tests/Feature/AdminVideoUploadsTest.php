<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminVideoUploadsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdminAndCategories(): array
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Payame Noor',
            'slug' => 'pnu',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => null,
            'title' => 'Physics Videos',
            'slug' => 'physics-videos',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        return [$admin, $institution, $category];
    }

    public function test_admin_can_upload_video_cover_preview_and_full_video(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Storage::fake('videos');
        Process::fake(fn () => Process::result(output: "120.0\n"));

        [$admin, $institution, $category] = $this->makeAdminAndCategories();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'My Video',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'published_at' => null,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 600),
            'preview_video' => UploadedFile::fake()->create('preview.mp4', 1200, 'video/mp4'),
            'video_file' => UploadedFile::fake()->create('full.mp4', 2400, 'video/mp4'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'type' => 'video',
            'title' => 'My Video',
        ]);

        $productId = (int) \DB::table('products')->where('title', 'My Video')->value('id');
        $this->assertNotSame(0, $productId);

        $product = \App\Models\Product::query()->findOrFail($productId);
        $this->assertNotNull($product->thumbnail_media_id);
        $this->assertSame($institution->id, (int) $product->institution_category_id);

        $this->assertDatabaseHas('product_categories', [
            'product_id' => (int) $productId,
            'category_id' => (int) $category->id,
        ]);

        $this->assertDatabaseHas('videos', [
            'product_id' => $productId,
        ]);

        $videoRow = \DB::table('videos')->where('product_id', $productId)->first();
        $this->assertNotNull($videoRow);
        $this->assertNotNull($videoRow->preview_media_id);
        $this->assertNotNull($videoRow->media_id);
        $this->assertSame(120, (int) $videoRow->duration_seconds);

        $cover = \App\Models\Media::query()->findOrFail((int) $product->thumbnail_media_id);
        $this->assertSame('public', $cover->disk);
        Storage::disk('public')->assertExists($cover->path);

        $preview = \App\Models\Media::query()->findOrFail((int) $videoRow->preview_media_id);
        $this->assertSame('videos', $preview->disk);
        Storage::disk('videos')->assertExists($preview->path);

        $full = \App\Models\Media::query()->findOrFail((int) $videoRow->media_id);
        $this->assertSame('videos', $full->disk);
        Storage::disk('videos')->assertExists($full->path);
        $this->assertSame(120, (int) $full->duration_seconds);
    }

    public function test_admin_cannot_submit_video_with_both_url_and_file(): void
    {
        Storage::fake('videos');

        [$admin, $institution, $category] = $this->makeAdminAndCategories();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'Invalid Video',
            'excerpt' => 'Invalid',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'video_url' => 'https://example.com/video.mp4',
            'video_file' => UploadedFile::fake()->create('full.mp4', 2400, 'video/mp4'),
        ]);

        $response->assertSessionHasErrors(['video_url', 'video_file']);
    }

    public function test_admin_cannot_submit_video_without_any_source(): void
    {
        [$admin, $institution, $category] = $this->makeAdminAndCategories();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'No Source Video',
            'excerpt' => 'Invalid',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'video_url' => '',
        ]);

        $response->assertSessionHasErrors(['video_url', 'video_file']);
    }
}
