<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookletUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_booklet_without_pdf_file(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin)->post(route('admin.booklets.store'), [
            'title' => 'Booklet without pdf',
            'excerpt' => 'Intro',
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => 800,
            'published_at' => null,
        ]);

        $response->assertSessionHasErrors(['booklet_file']);
    }

    public function test_admin_can_upload_booklet_cover_and_booklet_file(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin)->post(route('admin.booklets.store'), [
            'title' => 'Booklet 1',
            'excerpt' => 'Intro',
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => 800,
            'published_at' => null,
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 800, 600),
            'booklet_file' => UploadedFile::fake()->create('booklet.pdf', 200, 'application/pdf'),
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'type' => 'note',
            'title' => 'Booklet 1',
        ]);

        $productId = (int) \DB::table('products')->where('title', 'Booklet 1')->value('id');
        $this->assertNotSame(0, $productId);

        $product = \App\Models\Product::query()->findOrFail($productId);
        $this->assertNotNull($product->thumbnail_media_id);
        $this->assertNotSame('', (string) $product->slug);

        $this->assertDatabaseHas('product_parts', [
            'product_id' => $productId,
            'part_type' => 'file',
        ]);

        $partRow = \DB::table('product_parts')->where('product_id', $productId)->where('part_type', 'file')->first();
        $this->assertNotNull($partRow);
        $this->assertNotNull($partRow->media_id);

        $cover = \App\Models\Media::query()->findOrFail((int) $product->thumbnail_media_id);
        $this->assertSame('public', $cover->disk);
        Storage::disk('public')->assertExists($cover->path);

        $file = \App\Models\Media::query()->findOrFail((int) $partRow->media_id);
        $this->assertSame('local', $file->disk);
        Storage::disk('local')->assertExists($file->path);
    }
}
