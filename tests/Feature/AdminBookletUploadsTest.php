<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

        $productId = (int) \DB::table('products')->where('title', 'Booklet without pdf')->value('id');
        $this->assertNotSame(0, $productId);

        $product = \App\Models\Product::query()->findOrFail($productId);
        $this->assertSame($institution->id, (int) $product->institution_category_id);

        $this->assertDatabaseHas('product_categories', [
            'product_id' => (int) $productId,
            'category_id' => (int) $category->id,
        ]);

        $this->assertDatabaseMissing('product_parts', [
            'product_id' => $productId,
            'part_type' => 'file',
        ]);
    }

    public function test_admin_cannot_publish_booklet_without_pdf_file(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
            'intent' => 'publish',
            'title' => 'Booklet without pdf',
            'excerpt' => 'Intro',
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

        $response = $this->actingAs($admin, 'admin')->post(route('admin.booklets.store'), [
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
