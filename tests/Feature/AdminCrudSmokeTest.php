<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_scope_panel_to_user_and_clear_scope(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $userA = User::factory()->create(['phone' => '09120000001', 'name' => 'User A']);
        $userB = User::factory()->create(['phone' => '09120000002', 'name' => 'User B']);

        $this->actingAs($admin, 'admin')->post(route('admin.scope.user.store'), [
            'user_id' => $userA->id,
        ])->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('09120000001')
            ->assertDontSee('09120000002');

        $this->actingAs($admin, 'admin')->post(route('admin.scope.clear'))->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('09120000001')
            ->assertSee('09120000002');
    }

    public function test_scoped_admin_cannot_edit_other_user(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($admin, 'admin')->withSession(['admin_scoped_user_id' => $userA->id])
            ->get(route('admin.users.edit', $userB->id))
            ->assertNotFound();
    }

    public function test_admin_can_grant_and_revoke_product_access_for_user(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'Video X',
            'slug' => 'video-x',
            'excerpt' => 'x',
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'published',
            'base_price' => 0,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.users.accesses.store', $customer->id), [
            'product_id' => $product->id,
            'expires_days' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('product_accesses', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $accessId = (int) DB::table('product_accesses')
            ->where('user_id', $customer->id)
            ->where('product_id', $product->id)
            ->value('id');

        $this->actingAs($admin, 'admin')->delete(route('admin.users.accesses.destroy', [$customer->id, $accessId]))
            ->assertRedirect();

        $this->assertDatabaseMissing('product_accesses', [
            'id' => $accessId,
        ]);
    }

    public function test_admin_can_open_user_products_page_from_users_index(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create([
            'name' => 'Customer A',
            'phone' => '09121112233',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(route('admin.users.products', $customer->id));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.products', $customer->id))
            ->assertOk()
            ->assertSee('09121112233');
    }

    public function test_admin_can_update_user_roles_and_admin_flag_on_user_edit_page(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create([
            'name' => 'Customer',
            'phone' => '09123334444',
        ]);

        $editorRoleId = (int) Role::query()->create([
            'name' => 'editor',
            'description' => 'Editor',
        ])->id;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.users.update', $customer->id), [
                'name' => 'Customer',
                'phone' => '09123334444',
                'password' => '',
                'is_active' => '1',
                'is_admin' => '1',
                'role_ids' => [$editorRoleId],
            ])
            ->assertRedirect();

        $adminRoleId = (int) Role::query()->where('name', 'admin')->value('id');
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $customer->id,
            'role_id' => $adminRoleId,
        ]);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $customer->id,
            'role_id' => $editorRoleId,
        ]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.users.update', $customer->id), [
                'name' => 'Customer',
                'phone' => '09123334444',
                'password' => '',
                'is_active' => '1',
                'is_admin' => '0',
                'role_ids' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $customer->id,
            'role_id' => $adminRoleId,
        ]);
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $customer->id,
            'role_id' => $editorRoleId,
        ]);
    }

    public function test_admin_can_filter_users_list_by_admins_and_regular_users(): void
    {
        $admin = User::factory()->create([
            'phone' => '09120000999',
        ]);
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'phone' => '09120000011',
        ]);
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'phone' => '09120000022',
        ]);
        $adminUser->roles()->attach(Role::query()->where('name', 'admin')->value('id'));

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('09120000011')
            ->assertDontSee('09120000022');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['kind' => 'admins']))
            ->assertOk()
            ->assertSee('09120000022')
            ->assertDontSee('09120000011');
    }

    public function test_admin_can_create_and_update_booklet(): void
    {
        Storage::fake('local');

        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Azad University',
            'slug' => 'azad',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'note',
            'parent_id' => $institution->id,
            'title' => 'Math Notes',
            'slug' => 'azad-math-notes',
            'icon_key' => 'math',
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
            'booklet_file' => UploadedFile::fake()->create('booklet.pdf', 200, 'application/pdf'),
        ]);

        $bookletId = (int) Product::query()->where('slug', 'booklet-1')->value('id');
        $response->assertRedirect(route('admin.booklets.edit', $bookletId));

        $this->assertDatabaseHas('products', [
            'id' => $bookletId,
            'type' => 'note',
            'slug' => 'booklet-1',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.booklets.update', $bookletId), [
            'title' => 'Booklet 1 Updated',
            'excerpt' => 'Updated',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'published',
            'base_price' => 2000,
            'sale_price' => null,
            'published_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('admin.booklets.edit', $bookletId));

        $this->assertDatabaseHas('products', [
            'id' => $bookletId,
            'type' => 'note',
            'status' => 'published',
            'base_price' => 2000,
        ]);
    }

    public function test_admin_can_create_and_update_video(): void
    {
        Storage::fake('videos');
        Process::fake(fn () => Process::result(output: "120.0\n"));

        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Payame Noor',
            'slug' => 'pnu',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => $institution->id,
            'title' => 'Physics',
            'slug' => 'pnu-physics',
            'icon_key' => 'video',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'Video 1',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'published_at' => null,
        ]);

        $videoProductId = (int) Product::query()->where('slug', 'video-1')->value('id');
        $response->assertRedirect(route('admin.videos.edit', $videoProductId));

        $this->assertDatabaseHas('products', [
            'id' => $videoProductId,
            'type' => 'video',
            'slug' => 'video-1',
        ]);
        $this->assertDatabaseHas('videos', [
            'product_id' => $videoProductId,
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.videos.update', $videoProductId), [
            'title' => 'Video 1 Updated',
            'excerpt' => 'Updated',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'published',
            'base_price' => 2500,
            'sale_price' => 2000,
            'published_at' => now()->toDateTimeString(),
            'video_file' => UploadedFile::fake()->create('full.mp4', 2400, 'video/mp4'),
        ])->assertRedirect(route('admin.videos.edit', $videoProductId));

        $this->assertDatabaseHas('videos', [
            'product_id' => $videoProductId,
        ]);
    }

    public function test_admin_can_publish_video_with_video_url_without_uploading_file(): void
    {
        $institution = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Payame Noor',
            'slug' => 'pnu',
            'icon_key' => 'university',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $category = Category::query()->create([
            'type' => 'video',
            'parent_id' => $institution->id,
            'title' => 'Physics',
            'slug' => 'pnu-physics',
            'icon_key' => 'video',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'Video Url 1',
            'excerpt' => 'Intro',
            'institution_category_id' => $institution->id,
            'category_id' => $category->id,
            'status' => 'published',
            'base_price' => 1000,
            'sale_price' => null,
            'published_at' => now()->toDateTimeString(),
            'video_url' => 'https://example.com/videos/full.mp4',
        ]);

        $videoProductId = (int) Product::query()->where('slug', 'video-url-1')->value('id');
        $response->assertRedirect(route('admin.videos.edit', $videoProductId));

        $this->assertDatabaseHas('videos', [
            'product_id' => $videoProductId,
            'video_url' => 'https://example.com/videos/full.mp4',
        ]);
    }

    public function test_admin_can_create_update_and_close_ticket(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $response = $this->actingAs($admin, 'admin')->post(route('admin.tickets.store'), [
            'user_id' => $customer->id,
            'subject' => 'Help',
            'priority' => 'normal',
            'body' => 'Initial message',
        ]);

        $ticketId = (int) Ticket::query()->latest('id')->value('id');
        $response->assertRedirect(route('admin.tickets.show', $ticketId));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'user_id' => $customer->id,
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticketId,
            'sender_user_id' => $admin->id,
            'body' => 'Initial message',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.tickets.update', $ticketId), [
            'body' => 'Reply',
            'close' => '0',
        ])->assertRedirect(route('admin.tickets.show', $ticketId));

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticketId,
            'body' => 'Reply',
        ]);

        $this->actingAs($admin, 'admin')->put(route('admin.tickets.update', $ticketId), [
            'body' => '',
            'close' => '1',
        ])->assertRedirect(route('admin.tickets.show', $ticketId));

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'status' => 'closed',
        ]);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $this->actingAs($admin, 'admin')->post(route('admin.users.store'), [
            'name' => 'Test User',
            'phone' => '09120000000',
            'password' => 'secret123',
            'is_active' => '1',
            'is_admin' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'phone' => '09120000000',
            'name' => 'Test User',
        ]);
    }

    public function test_admin_can_create_and_update_post_without_slug(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.posts.store'), [
            'title' => 'My Post 1',
            'excerpt' => 'Intro',
            'body' => '<p>Hello <strong>world</strong></p>',
            'status' => 'draft',
            'published_at' => null,
        ]);

        $postId = (int) Post::query()->where('title', 'My Post 1')->value('id');
        $response->assertRedirect(route('admin.posts.edit', $postId));

        $post = Post::query()->findOrFail($postId);
        $this->assertSame('my-post-1', $post->slug);
        $this->assertSame('<p>Hello <strong>world</strong></p>', $post->body);

        $this->actingAs($admin, 'admin')->put(route('admin.posts.update', $postId), [
            'title' => 'My Post 1 Updated',
            'excerpt' => 'Updated',
            'body' => '<p>Updated body</p>',
            'status' => 'published',
            'published_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('admin.posts.edit', $postId));

        $post->refresh();
        $this->assertSame('my-post-1', $post->slug);
        $this->assertSame('<p>Updated body</p>', $post->body);
    }

    public function test_admin_can_upload_cover_image_for_post_on_create_and_update(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $cover1 = \Illuminate\Http\UploadedFile::fake()->image('cover-1.jpg', 1200, 675);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.posts.store'), [
            'title' => 'My Post With Cover',
            'excerpt' => 'Intro',
            'body' => '<p>Body</p>',
            'status' => 'draft',
            'published_at' => null,
            'cover_image' => $cover1,
        ]);

        $postId = (int) Post::query()->where('title', 'My Post With Cover')->value('id');
        $response->assertRedirect(route('admin.posts.edit', $postId));

        $post = Post::query()->findOrFail($postId);
        $this->assertNotNull($post->cover_media_id);

        $media1 = \App\Models\Media::query()->findOrFail((int) $post->cover_media_id);
        $this->assertSame('public', $media1->disk);
        $this->assertSame('cover-1.jpg', $media1->original_name);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($media1->path));

        $cover2 = \Illuminate\Http\UploadedFile::fake()->image('cover-2.jpg', 1200, 675);

        $this->actingAs($admin, 'admin')->post(route('admin.posts.update', $postId), [
            '_method' => 'put',
            'title' => 'My Post With Cover Updated',
            'excerpt' => 'Intro 2',
            'body' => '<p>Body 2</p>',
            'status' => 'published',
            'published_at' => now()->toDateTimeString(),
            'cover_image' => $cover2,
        ])->assertRedirect(route('admin.posts.edit', $postId));

        $post->refresh();
        $this->assertNotNull($post->cover_media_id);
        $this->assertNotSame($media1->id, (int) $post->cover_media_id);

        $media2 = \App\Models\Media::query()->findOrFail((int) $post->cover_media_id);
        $this->assertSame('public', $media2->disk);
        $this->assertSame('cover-2.jpg', $media2->original_name);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($media2->path));
    }

    public function test_admin_cannot_create_post_without_body(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $this->actingAs($admin, 'admin')
            ->from(route('admin.posts.create'))
            ->post(route('admin.posts.store'), [
                'title' => 'My Post Missing Body',
                'excerpt' => 'Intro',
                'status' => 'draft',
                'published_at' => null,
            ])
            ->assertRedirect(route('admin.posts.create'))
            ->assertSessionHasErrors(['body']);

        $this->assertDatabaseMissing('posts', [
            'title' => 'My Post Missing Body',
        ]);
    }

    public function test_admin_can_upload_media_file_and_metadata_is_saved(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $file = \Illuminate\Http\UploadedFile::fake()->image('test-image.png', 120, 80);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.media.store'), [
                'disk' => 'public',
                'file' => $file,
            ]);

        $mediaId = (int) \App\Models\Media::query()->where('original_name', 'test-image.png')->value('id');
        $response->assertRedirect(route('admin.media.show', $mediaId));

        $media = \App\Models\Media::query()->findOrFail($mediaId);
        $this->assertSame('public', $media->disk);
        $this->assertSame('test-image.png', $media->original_name);
        $this->assertSame('image/png', $media->mime_type);
        $this->assertNotNull($media->size);
        $this->assertNotSame('', (string) $media->path);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($media->path));
    }

    public function test_admin_can_upload_wysiwyg_image_and_get_url(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $file = \Illuminate\Http\UploadedFile::fake()->image('editor.jpg', 50, 50);

        $response = $this->actingAs($admin, 'admin')
            ->postJson(route('admin.media.wysiwyg'), [
                'file' => $file,
            ]);

        $response->assertOk()->assertJsonStructure(['url', 'media_id']);

        $mediaId = (int) $response->json('media_id');
        $media = \App\Models\Media::query()->findOrFail($mediaId);
        $this->assertSame('public', $media->disk);
        $this->assertSame('editor.jpg', $media->original_name);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($media->path));
    }

    public function test_admin_can_create_and_update_category_without_slug(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Math 1',
            'icon_key' => 'math',
            'description' => '',
            'is_active' => '1',
            'sort_order' => 0,
        ]);

        $categoryId = (int) Category::query()->where('type', 'note')->where('title', 'Math 1')->value('id');
        $response->assertRedirect(route('admin.categories.edit', $categoryId));

        $category = Category::query()->findOrFail($categoryId);
        $this->assertSame('math-1', $category->slug);

        $this->actingAs($admin, 'admin')->put(route('admin.categories.update', $categoryId), [
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Math 1 Updated',
            'icon_key' => 'math',
            'description' => '',
            'is_active' => '1',
            'sort_order' => 0,
        ])->assertRedirect(route('admin.categories.edit', $categoryId));

        $category->refresh();
        $this->assertSame('math-1', $category->slug);
    }

    public function test_admin_can_upload_category_cover_image(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $file = \Illuminate\Http\UploadedFile::fake()->image('cover.jpg', 200, 120);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Cover Category',
            'icon_key' => 'math',
            'description' => '',
            'cover_image' => $file,
            'is_active' => '1',
            'sort_order' => 0,
        ]);

        $categoryId = (int) Category::query()->where('type', 'note')->where('title', 'Cover Category')->value('id');
        $response->assertRedirect(route('admin.categories.edit', $categoryId));

        $category = Category::query()->findOrFail($categoryId);
        $this->assertNotNull($category->cover_media_id);

        $media = \App\Models\Media::query()->findOrFail((int) $category->cover_media_id);
        $this->assertSame('public', $media->disk);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists((string) $media->path));
    }

    public function test_admin_categories_index_groups_by_type_and_shows_hierarchy(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $noteRoot = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Root',
            'slug' => 'root',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'note',
            'parent_id' => $noteRoot->id,
            'title' => 'Child',
            'slug' => 'child',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Uni',
            'slug' => 'uni',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.categories.index'));

        $response
            ->assertOk()
            ->assertSee('نوع: جزوه')
            ->assertSee('نوع: دانشگاه')
            ->assertSeeInOrder(['Root', 'Child']);
    }

    public function test_admin_categories_disallow_duplicate_titles_per_type_and_block_delete_when_has_products(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Math',
            'description' => '',
            'is_active' => '1',
            'sort_order' => 0,
        ])->assertRedirect();

        $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Math',
            'description' => '',
            'is_active' => '1',
            'sort_order' => 0,
        ])->assertSessionHasErrors('title');

        $this->actingAs($admin, 'admin')->post(route('admin.categories.store'), [
            'type' => 'video',
            'parent_id' => null,
            'title' => 'Math',
            'description' => '',
            'is_active' => '1',
            'sort_order' => 0,
        ])->assertRedirect();

        $categoryId = (int) Category::query()->where('type', 'note')->where('title', 'Math')->value('id');

        $productInCategory = Product::query()->create([
            'type' => 'note',
            'title' => 'Product In Category',
            'slug' => 'product-in-category',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);
        $productInCategory->categories()->attach($categoryId);

        $otherProduct = Product::query()->create([
            'type' => 'note',
            'title' => 'Other Product',
            'slug' => 'other-product',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee(route('admin.products.index', ['category' => $categoryId]))
            ->assertSee('>1</a>', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index', ['category' => $categoryId]))
            ->assertOk()
            ->assertSee('Product In Category')
            ->assertDontSee('Other Product');

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.categories.destroy', $categoryId))
            ->assertRedirect(route('admin.categories.edit', $categoryId));

        $this->assertDatabaseHas('categories', [
            'id' => $categoryId,
        ]);
    }

    public function test_admin_categories_index_orders_types_as_requested(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        Category::query()->create([
            'type' => 'ticket',
            'parent_id' => null,
            'title' => 'Ticket Cat',
            'slug' => 'ticket-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'post',
            'parent_id' => null,
            'title' => 'Post Cat',
            'slug' => 'post-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'course',
            'parent_id' => null,
            'title' => 'Course Cat',
            'slug' => 'course-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Note Cat',
            'slug' => 'note-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        Category::query()->create([
            'type' => 'video',
            'parent_id' => null,
            'title' => 'Video Cat',
            'slug' => 'video-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSeeInOrder(['نوع: ویدیو', 'نوع: جزوه', 'نوع: دوره', 'نوع: مقاله', 'نوع: تیکت']);
    }

    public function test_admin_categories_ticket_type_has_no_related_count_column(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        Category::query()->create([
            'type' => 'ticket',
            'parent_id' => null,
            'title' => 'Ticket Cat',
            'slug' => 'ticket-cat',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertDontSee('<th>محصولات</th>', false)
            ->assertDontSee('<th>مقالات</th>', false);
    }

    public function test_admin_categories_post_type_shows_posts_count_and_filters_posts_page(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $postCategory = Category::query()->create([
            'type' => 'post',
            'parent_id' => null,
            'title' => 'Articles',
            'slug' => 'articles',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'admin')->post(route('admin.posts.store'), [
            'title' => 'My Categorized Post',
            'excerpt' => 'Intro',
            'body' => '<p>Body</p>',
            'status' => 'draft',
            'published_at' => null,
        ])->assertRedirect();

        $postId = (int) Post::query()->where('title', 'My Categorized Post')->value('id');
        DB::table('post_categories')->insert([
            'post_id' => $postId,
            'category_id' => (int) $postCategory->id,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee(route('admin.posts.index', ['category' => (int) $postCategory->id]))
            ->assertSee('>1</a>', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.posts.index', ['category' => (int) $postCategory->id]))
            ->assertOk()
            ->assertViewHas('posts', fn ($posts) => $posts->getCollection()->contains('id', $postId));
    }

    public function test_admin_can_quickly_change_product_category_from_filtered_products_list(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $catA = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Cat A',
            'slug' => 'cat-a',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $catB = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Cat B',
            'slug' => 'cat-b',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::query()->create([
            'type' => 'note',
            'title' => 'My Product',
            'slug' => 'my-product',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);
        $product->categories()->attach((int) $catA->id);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.products.category.update', $product->id), [
                'category_id' => (int) $catB->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('product_categories', [
            'product_id' => (int) $product->id,
            'category_id' => (int) $catA->id,
        ]);
        $this->assertDatabaseHas('product_categories', [
            'product_id' => (int) $product->id,
            'category_id' => (int) $catB->id,
        ]);
    }

    public function test_admin_product_edit_uses_product_values_not_old_input_for_institution_and_category(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $institutionA = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Institution A',
            'slug' => 'institution-a',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $institutionB = Category::query()->create([
            'type' => 'institution',
            'parent_id' => null,
            'title' => 'Institution B',
            'slug' => 'institution-b',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $categoryA = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Category A',
            'slug' => 'category-a',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $categoryB = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'Category B',
            'slug' => 'category-b',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $productA = Product::query()->create([
            'type' => 'note',
            'title' => 'Product A',
            'slug' => 'product-a',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'institution_category_id' => (int) $institutionA->id,
            'status' => 'published',
            'base_price' => 1000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);
        $productA->categories()->attach((int) $categoryA->id);

        $productB = Product::query()->create([
            'type' => 'note',
            'title' => 'Product B',
            'slug' => 'product-b',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'institution_category_id' => (int) $institutionB->id,
            'status' => 'published',
            'base_price' => 2000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);
        $productB->categories()->attach((int) $categoryB->id);

        $this->actingAs($admin, 'admin')
            ->withSession([
                '_old_input' => [
                    'institution_category_id' => (int) $institutionA->id,
                    'category_id' => (int) $categoryA->id,
                ],
            ])
            ->get(route('admin.products.edit', $productB->id))
            ->assertOk()
            ->assertSee('name="institution_category_id"', false)
            ->assertSee('value="'.$institutionB->id.'" selected', false)
            ->assertDontSee('value="'.$institutionA->id.'" selected', false)
            ->assertSee('name="category_id"', false)
            ->assertSee('value="'.$categoryB->id.'" selected', false)
            ->assertDontSee('value="'.$categoryA->id.'" selected', false);
    }

    public function test_admin_sidebar_shows_unread_tickets_and_pending_orders_badges(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'A',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => null,
            'meta' => [],
        ]);

        Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'B',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => null,
            'meta' => [],
        ]);

        foreach (range(1, 3) as $i) {
            Order::query()->create([
                'order_number' => 'ORD-'.$i,
                'user_id' => $customer->id,
                'status' => 'pending',
                'currency' => 'IRR',
                'subtotal_amount' => 1000,
                'discount_amount' => 0,
                'total_amount' => 1000,
                'payable_amount' => 1000,
                'placed_at' => now(),
                'paid_at' => null,
                'cancelled_at' => null,
                'meta' => [],
            ]);
        }

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<span class="badge badge--brand" style="margin-right: 8px;">2</span>', false)
            ->assertSee('<span class="badge badge--brand" style="margin-right: 8px;">3</span>', false);
    }

    public function test_admin_dashboard_shows_products_count_and_session_analytics(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        Product::query()->create([
            'type' => 'video',
            'title' => 'Video 1',
            'slug' => 'video-1',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'draft',
            'base_price' => 0,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => null,
            'meta' => [],
        ]);

        Product::query()->create([
            'type' => 'video',
            'title' => 'Video 2',
            'slug' => 'video-2',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'draft',
            'base_price' => 0,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => null,
            'meta' => [],
        ]);

        $payloadA = base64_encode(serialize([
            'analytics' => [
                'country' => 'IR',
                'device' => 'mobile',
            ],
        ]));

        $payloadB = base64_encode(serialize([
            'analytics' => [
                'country' => 'US',
                'device' => 'web',
            ],
        ]));

        DB::table('sessions')->insert([
            [
                'id' => 'sess-a',
                'user_id' => null,
                'ip_address' => '1.1.1.1',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
                'payload' => $payloadA,
                'last_activity' => now()->timestamp,
            ],
            [
                'id' => 'sess-b',
                'user_id' => null,
                'ip_address' => '2.2.2.2',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'payload' => $payloadB,
                'last_activity' => now()->timestamp,
            ],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('محصولات')
            ->assertSee('2')
            ->assertSee('آنالیتیکس نشست‌ها')
            ->assertSee('IR')
            ->assertSee('US')
            ->assertSee('50% / 50%');
    }

    public function test_admin_order_edit_page_localizes_status_options(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $order = Order::query()->create([
            'order_number' => 'ORD-LOCALIZE-1',
            'user_id' => $customer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payable_amount' => 1000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.edit', $order->id))
            ->assertOk()
            ->assertSee('در انتظار پرداخت')
            ->assertSee('در انتظار تایید')
            ->assertSee('تایید شده')
            ->assertSee('رد شده')
            ->assertSee('لغو شده');
    }

    public function test_admin_orders_index_shows_user_name_and_phone_instead_of_id(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create([
            'name' => 'نام نمایشی',
            'phone' => '09120000001',
        ]);
        $customer->forceFill([
            'first_name' => 'علی',
            'last_name' => 'احمدی',
        ])->save();

        $order = Order::query()->create([
            'order_number' => 'ORD-USER-1',
            'user_id' => $customer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payable_amount' => 1000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [],
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'mock',
            'status' => 'initiated',
            'amount' => 1000,
            'currency' => 'IRR',
            'authority' => null,
            'reference_id' => null,
            'paid_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('علی احمدی')
            ->assertSee('09120000001');

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'Cart Product',
            'slug' => 'cart-product',
            'excerpt' => null,
            'description' => null,
            'thumbnail_media_id' => null,
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => null,
            'meta' => [],
        ]);

        $cart = Cart::query()->create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
            'currency' => 'IRR',
            'meta' => [],
        ]);

        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 5000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('سبدهای فعال')
            ->assertSee('09120000001')
            ->assertSee('10,000', false);
    }

    public function test_admin_products_index_shows_sales_count_and_sorts_by_it_desc(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $productLow = Product::query()->create([
            'type' => 'video',
            'title' => 'Product Low',
            'slug' => 'product-low',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $productHigh = Product::query()->create([
            'type' => 'video',
            'title' => 'Product High',
            'slug' => 'product-high',
            'status' => 'published',
            'base_price' => 1000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $paidOrder = Order::query()->create([
            'order_number' => 'ORD-SALES-1',
            'user_id' => $customer->id,
            'status' => 'paid',
            'currency' => 'IRR',
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'payable_amount' => 0,
            'placed_at' => now(),
            'paid_at' => now(),
            'cancelled_at' => null,
            'meta' => [],
        ]);

        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'product_id' => $productLow->id,
            'product_type' => (string) $productLow->type,
            'product_title' => (string) $productLow->title,
            'quantity' => 2,
            'unit_price' => 1000,
            'total_price' => 2000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        OrderItem::query()->create([
            'order_id' => $paidOrder->id,
            'product_id' => $productHigh->id,
            'product_type' => (string) $productHigh->type,
            'product_title' => (string) $productHigh->title,
            'quantity' => 5,
            'unit_price' => 1000,
            'total_price' => 5000,
            'currency' => 'IRR',
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('تعداد فروش')
            ->assertSeeInOrder(['Product High', 'Product Low']);
    }

    public function test_admin_order_show_hides_id_and_renders_items_with_product_title_and_price(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'محصول تست',
            'slug' => 'order-item-product',
            'status' => 'published',
            'base_price' => 12000,
            'currency' => 'IRT',
            'published_at' => now(),
            'meta' => [],
        ]);

        $order = Order::query()->create([
            'order_number' => 'ORD-ITEMS-1',
            'user_id' => $customer->id,
            'status' => 'pending',
            'currency' => 'IRT',
            'subtotal_amount' => 12000,
            'discount_amount' => 0,
            'total_amount' => 12000,
            'payable_amount' => 12000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [],
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_type' => 'video',
            'product_title' => $product->title,
            'quantity' => 1,
            'unit_price' => 12000,
            'total_price' => 12000,
            'currency' => 'IRT',
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.orders.show', $order->id))
            ->assertOk()
            ->assertDontSee('شناسه:')
            ->assertDontSee('<th>تعداد</th>', false)
            ->assertSee('محصول تست')
            ->assertSee('<span class="money__amount" dir="ltr">12,000</span>', false)
            ->assertSee('<span class="money__unit">تومان</span>', false);
    }

    public function test_admin_payment_pages_localize_gateway_and_status(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $order = Order::query()->create([
            'order_number' => 'ORD-LOCALIZE-2',
            'user_id' => $customer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payable_amount' => 1000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [],
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'mock',
            'status' => 'initiated',
            'amount' => 1000,
            'currency' => 'IRR',
            'authority' => null,
            'reference_id' => null,
            'paid_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('درگاه آزمایشی')
            ->assertSee('در انتظار پرداخت');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.payments.show', $payment->id))
            ->assertOk()
            ->assertSee('درگاه آزمایشی')
            ->assertSee('در انتظار پرداخت');
    }

    public function test_admin_payments_index_shows_user_and_hides_order_column(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create([
            'name' => 'نام نمایشی',
            'phone' => '09120000002',
        ]);
        $customer->forceFill([
            'first_name' => 'زهرا',
            'last_name' => 'محمدی',
        ])->save();

        $order = Order::query()->create([
            'order_number' => 'ORD-PAY-USER-1',
            'user_id' => $customer->id,
            'status' => 'pending',
            'currency' => 'IRR',
            'subtotal_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 1000,
            'payable_amount' => 1000,
            'placed_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'meta' => [],
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'gateway' => 'mock',
            'status' => 'initiated',
            'amount' => 1000,
            'currency' => 'IRR',
            'authority' => null,
            'reference_id' => null,
            'paid_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertDontSee('<th>سفارش</th>', false)
            ->assertSee('زهرا محمدی')
            ->assertSee('09120000002');
    }

    public function test_admin_ticket_pages_localize_status_and_priority(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $ticket = Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'A',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => null,
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertSee('باز')
            ->assertSee('معمولی');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee('باز')
            ->assertSee('معمولی');
    }

    public function test_admin_tables_hide_slug_and_id_and_show_correct_prices_and_user_names(): void
    {
        Setting::query()->updateOrCreate(
            ['key' => 'commerce.currency', 'group' => 'commerce'],
            ['value' => 'IRT']
        );

        $admin = User::factory()->create([
            'name' => 'ادمین تست',
        ]);
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $course = Product::query()->create([
            'type' => 'course',
            'title' => 'دوره ۱',
            'slug' => 'course-1',
            'status' => 'published',
            'base_price' => 80000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        Product::query()->create([
            'type' => 'course',
            'title' => 'دوره تخفیف درصدی',
            'slug' => 'course-percent-off',
            'status' => 'published',
            'base_price' => 100000,
            'discount_type' => 'percent',
            'discount_value' => 25,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه تخفیف مبلغی',
            'slug' => 'note-amount-off',
            'status' => 'published',
            'base_price' => 80000,
            'discount_type' => 'amount',
            'discount_value' => 10000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $booklet = Product::query()->create([
            'type' => 'note',
            'title' => 'جزوه ۱',
            'slug' => 'note-1',
            'status' => 'published',
            'base_price' => 80000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $video = Product::query()->create([
            'type' => 'video',
            'title' => 'ویدیو ۱',
            'slug' => 'video-1',
            'status' => 'published',
            'base_price' => 80000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $post = Post::query()->create([
            'title' => 'مقاله ۱',
            'slug' => 'post-1',
            'status' => 'published',
            'published_at' => now(),
            'meta' => [],
        ]);

        $customer = User::factory()->create([
            'name' => 'نام نمایشی',
            'phone' => '09120000003',
        ]);
        $customer->forceFill([
            'first_name' => 'علی',
            'last_name' => 'کاظمی',
        ])->save();

        $ticket = Ticket::query()->create([
            'user_id' => $customer->id,
            'subject' => 'موضوع تیکت',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => null,
            'meta' => [],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $customer->id,
            'body' => 'پیام کاربر',
            'meta' => [],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $admin->id,
            'body' => 'پاسخ ادمین',
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertDontSee('<th>شناسه</th>', false)
            ->assertDontSee('اسلاگ')
            ->assertDontSee('course-1')
            ->assertSee('8,000', false)
            ->assertSee('تومان')
            ->assertSee('25%', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.booklets.index'))
            ->assertOk()
            ->assertDontSee('note-1')
            ->assertSee('8,000', false)
            ->assertSee('تومان')
            ->assertSee('1,000', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.videos.index'))
            ->assertOk()
            ->assertDontSee('video-1')
            ->assertSee('8,000', false)
            ->assertSee('تومان');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertDontSee('post-1');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertDontSee('<th>شناسه</th>', false)
            ->assertDontSee('<th>اسلاگ</th>', false)
            ->assertSee('دوره')
            ->assertSee('جزوه')
            ->assertSee('ویدیو')
            ->assertSee('8,000', false)
            ->assertSee('تومان')
            ->assertSee('25%', false)
            ->assertSee('1,000', false)
            ->assertDontSee('ویرایش');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertDontSee('<th>شناسه</th>', false)
            ->assertSee('علی کاظمی')
            ->assertDontSee('>'.$customer->id.'<', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee('علی کاظمی')
            ->assertSee('پیام کاربر')
            ->assertSee('ادمین تست')
            ->assertSee('پاسخ ادمین');

        $category = Category::query()->create([
            'type' => 'note',
            'parent_id' => null,
            'title' => 'ریاضی ۱',
            'slug' => 'math-1',
            'icon_key' => 'math',
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('admin-grid-2', false)
            ->assertDontSee('<th>شناسه</th>', false)
            ->assertDontSee('<th>ترتیب</th>', false)
            ->assertDontSee('math-1');
    }

    public function test_admin_index_pages_paginate_40_items_per_page(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        User::factory()->count(45)->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->perPage() === 40);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->perPage() === 40);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.videos.index'))
            ->assertOk()
            ->assertViewHas('videos', fn ($videos) => $videos->perPage() === 40);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.booklets.index'))
            ->assertOk()
            ->assertViewHas('booklets', fn ($booklets) => $booklets->perPage() === 40);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertViewHas('posts', fn ($posts) => $posts->perPage() === 40);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.surveys.index'))
            ->assertOk()
            ->assertViewHas('surveys', fn ($surveys) => $surveys->perPage() === 40);
    }

    public function test_admin_stub_pages_are_not_used_for_core_resources(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $stubMessage = 'این صفحه در مرحله فعلی به صورت استاب آماده شده است.';

        $this->actingAs($admin, 'admin')->get(route('admin.categories.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.products.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.courses.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.orders.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.payments.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.coupons.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.banners.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.social-links.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.media.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.roles.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin, 'admin')->get(route('admin.permissions.index'))->assertOk()->assertDontSee($stubMessage);
    }

    public function test_admin_can_create_coupon_scoped_to_products(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $product = Product::query()->create([
            'type' => 'video',
            'title' => 'محصول تست',
            'slug' => 'coupon-scope-product',
            'status' => 'published',
            'base_price' => 100000,
            'currency' => 'IRR',
            'published_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.coupons.store'), [
                'code' => 'off1a2',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'starts_at' => '',
                'ends_at' => '',
                'usage_limit' => '',
                'per_user_limit' => '',
                'is_active' => '1',
                'apply_all_products' => '0',
                'product_ids' => [$product->id],
            ])
            ->assertRedirect();

        $coupon = Coupon::query()->firstOrFail();
        $this->assertSame('OFF1A2', (string) $coupon->code);
        $this->assertSame([$product->id], array_values(($coupon->meta ?? [])['product_ids'] ?? []));
    }
}
