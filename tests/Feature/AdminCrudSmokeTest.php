<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Post;
use App\Models\Product;
use App\Models\Role;
use App\Models\Ticket;
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

    public function test_admin_can_create_and_update_booklet(): void
    {
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

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.videos.store'), [
            'title' => 'Video 1',
            'excerpt' => 'Intro',
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
            'status' => 'draft',
            'published_at' => null,
        ]);

        $postId = (int) Post::query()->where('title', 'My Post 1')->value('id');
        $response->assertRedirect(route('admin.posts.edit', $postId));

        $post = Post::query()->findOrFail($postId);
        $this->assertSame('my-post-1', $post->slug);

        $this->actingAs($admin, 'admin')->put(route('admin.posts.update', $postId), [
            'title' => 'My Post 1 Updated',
            'excerpt' => 'Updated',
            'status' => 'published',
            'published_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('admin.posts.edit', $postId));

        $post->refresh();
        $this->assertSame('my-post-1', $post->slug);
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
            ->assertSee('نوع: note')
            ->assertSee('نوع: institution')
            ->assertSeeInOrder(['Root', 'Child'])
            ->assertSee('&nbsp;&nbsp;&nbsp;&nbsp;Child', false);
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
}
