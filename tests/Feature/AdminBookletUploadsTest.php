<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBookletUploadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_booklet_draft_without_pdf_file(): void
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
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Math Notes',
            'slug' => 'math-notes',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
            'title' => 'Booklet without pdf',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => 800,
            'published_at' => null,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'type' => 'note',
            'title' => 'Booklet without pdf',
            'status' => 'draft',
        ]);

        $productId = (int) DB::table('products')->where('title', 'Booklet without pdf')->value('id');
        $this->assertNotSame(0, $productId);

        $product = \App\Models\Product::query()->findOrFail($productId);
        $this->assertSame($institution->id, (int) $product->institution_category_id);

        $this->assertDatabaseHas('product_categories', [
            'product_id' => (int) $productId,
            'category_id' => (int) $category->id,
        ]);

        $this->assertDatabaseHas('booklets', [
            'product_id' => $productId,
        ]);

        $this->assertDatabaseHas('booklets', [
            'product_id' => $productId,
            'file_media_id' => null,
        ]);
    }

    public function test_admin_cannot_publish_booklet_without_pdf_file(): void
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
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'Math Notes',
            'slug' => 'azad-math-notes',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
            'intent' => 'publish',
            'title' => 'Booklet without pdf',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
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
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'Math Notes',
            'slug' => 'azad-math-notes',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
            'title' => 'Booklet 1',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
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

        $productId = (int) DB::table('products')->where('title', 'Booklet 1')->value('id');
        $this->assertNotSame(0, $productId);

        $product = \App\Models\Product::query()->findOrFail($productId);
        $this->assertNotNull($product->thumbnail_media_id);
        $this->assertNotSame('', (string) $product->slug);

        $this->assertDatabaseHas('booklets', [
            'product_id' => $productId,
        ]);

        $booklet = \App\Models\Booklet::query()->where('product_id', $productId)->first();
        $this->assertNotNull($booklet);
        $this->assertNotNull($booklet->file_media_id);

        $cover = \App\Models\Media::query()->findOrFail((int) $product->thumbnail_media_id);
        $this->assertSame('public', $cover->disk);
        $this->assertTrue(Storage::disk('public')->exists($cover->path));

        $file = \App\Models\Media::query()->findOrFail((int) $booklet->file_media_id);
        $this->assertSame('local', $file->disk);
        $this->assertTrue(Storage::disk('local')->exists($file->path));
    }

    public function test_admin_can_upload_booklet_preview_images_and_sample_pdf(): void
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
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'Math Notes',
            'slug' => 'azad-math-notes',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
            'title' => 'Booklet With Preview',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'published_at' => null,
            'booklet_file' => UploadedFile::fake()->create('booklet.pdf', 200, 'application/pdf'),
            'sample_pdf' => UploadedFile::fake()->create('sample.pdf', 50, 'application/pdf'),
            'preview_images' => [
                UploadedFile::fake()->image('p1.jpg', 200, 120),
                UploadedFile::fake()->image('p2.jpg', 200, 120),
            ],
        ]);

        $response->assertRedirect();

        $productId = (int) DB::table('products')->where('title', 'Booklet With Preview')->value('id');
        $this->assertNotSame(0, $productId);

        $booklet = \App\Models\Booklet::query()->where('product_id', $productId)->first();
        $this->assertNotNull($booklet);
        $this->assertNotNull($booklet->sample_pdf_media_id);
        $this->assertIsArray($booklet->preview_image_media_ids);
        $this->assertCount(2, $booklet->preview_image_media_ids);

        $sample = \App\Models\Media::query()->findOrFail((int) $booklet->sample_pdf_media_id);
        $this->assertSame('public', $sample->disk);
        $this->assertTrue(Storage::disk('public')->exists($sample->path));

        foreach ($booklet->preview_image_media_ids as $id) {
            $media = \App\Models\Media::query()->findOrFail((int) $id);
            $this->assertSame('public', $media->disk);
            $this->assertTrue(Storage::disk('public')->exists($media->path));
        }
    }
}
