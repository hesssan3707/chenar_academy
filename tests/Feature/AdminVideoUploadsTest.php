<?php

namespace Tests\Feature;

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

    public function test_admin_can_upload_video_cover_preview_and_full_video(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        Process::fake(fn () => Process::result(output: "120.0\n"));

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin)->post(route('admin.videos.store'), [
            'title' => 'My Video',
            'excerpt' => 'Intro',
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
        $this->assertSame('local', $preview->disk);
        Storage::disk('local')->assertExists($preview->path);

        $full = \App\Models\Media::query()->findOrFail((int) $videoRow->media_id);
        $this->assertSame('local', $full->disk);
        Storage::disk('local')->assertExists($full->path);
        $this->assertSame(120, (int) $full->duration_seconds);
    }
}
