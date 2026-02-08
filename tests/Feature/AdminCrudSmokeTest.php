<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        $this->actingAs($admin)->post(route('admin.scope.user.store'), [
            'user_id' => $userA->id,
        ])->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('09120000001')
            ->assertDontSee('09120000002');

        $this->actingAs($admin)->post(route('admin.scope.clear'))->assertRedirect();

        $this->actingAs($admin)
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

        $this->actingAs($admin)->withSession(['admin_scoped_user_id' => $userA->id])
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

        $this->actingAs($admin)->post(route('admin.users.accesses.store', $customer->id), [
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

        $this->actingAs($admin)->delete(route('admin.users.accesses.destroy', [$customer->id, $accessId]))
            ->assertRedirect();

        $this->assertDatabaseMissing('product_accesses', [
            'id' => $accessId,
        ]);
    }

    public function test_admin_can_create_and_update_booklet(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin)->post(route('admin.booklets.store'), [
            'title' => 'Booklet 1',
            'slug' => 'booklet-1',
            'excerpt' => 'Intro',
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => 800,
            'currency' => 'IRR',
            'published_at' => null,
        ]);

        $bookletId = (int) Product::query()->where('slug', 'booklet-1')->value('id');
        $response->assertRedirect(route('admin.booklets.edit', $bookletId));

        $this->assertDatabaseHas('products', [
            'id' => $bookletId,
            'type' => 'note',
            'slug' => 'booklet-1',
        ]);

        $this->actingAs($admin)->put(route('admin.booklets.update', $bookletId), [
            'title' => 'Booklet 1 Updated',
            'slug' => 'booklet-1',
            'excerpt' => 'Updated',
            'status' => 'published',
            'base_price' => 2000,
            'sale_price' => null,
            'currency' => 'IRR',
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
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $response = $this->actingAs($admin)->post(route('admin.videos.store'), [
            'title' => 'Video 1',
            'slug' => 'video-1',
            'excerpt' => 'Intro',
            'status' => 'draft',
            'base_price' => 1000,
            'sale_price' => null,
            'currency' => 'IRR',
            'published_at' => null,
            'duration_seconds' => 120,
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
            'duration_seconds' => 120,
        ]);

        $this->actingAs($admin)->put(route('admin.videos.update', $videoProductId), [
            'title' => 'Video 1 Updated',
            'slug' => 'video-1',
            'excerpt' => 'Updated',
            'status' => 'published',
            'base_price' => 2500,
            'sale_price' => 2000,
            'currency' => 'IRR',
            'published_at' => now()->toDateTimeString(),
            'duration_seconds' => 180,
        ])->assertRedirect(route('admin.videos.edit', $videoProductId));

        $this->assertDatabaseHas('videos', [
            'product_id' => $videoProductId,
            'duration_seconds' => 180,
        ]);
    }

    public function test_admin_can_create_update_and_close_ticket(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $customer = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.tickets.store'), [
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

        $this->actingAs($admin)->put(route('admin.tickets.update', $ticketId), [
            'body' => 'Reply',
            'close' => '0',
        ])->assertRedirect(route('admin.tickets.show', $ticketId));

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticketId,
            'body' => 'Reply',
        ]);

        $this->actingAs($admin)->put(route('admin.tickets.update', $ticketId), [
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

        $this->actingAs($admin)->post(route('admin.users.store'), [
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

    public function test_admin_index_pages_paginate_40_items_per_page(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        User::factory()->count(45)->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertViewHas('users', fn ($users) => $users->perPage() === 40);

        $this->actingAs($admin)
            ->get(route('admin.tickets.index'))
            ->assertOk()
            ->assertViewHas('tickets', fn ($tickets) => $tickets->perPage() === 40);

        $this->actingAs($admin)
            ->get(route('admin.videos.index'))
            ->assertOk()
            ->assertViewHas('videos', fn ($videos) => $videos->perPage() === 40);

        $this->actingAs($admin)
            ->get(route('admin.booklets.index'))
            ->assertOk()
            ->assertViewHas('booklets', fn ($booklets) => $booklets->perPage() === 40);

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertViewHas('posts', fn ($posts) => $posts->perPage() === 40);

        $this->actingAs($admin)
            ->get(route('admin.surveys.index'))
            ->assertOk()
            ->assertViewHas('surveys', fn ($surveys) => $surveys->perPage() === 40);
    }

    public function test_admin_stub_pages_are_not_used_for_core_resources(): void
    {
        $admin = User::factory()->create();
        $admin->roles()->attach(Role::create(['name' => 'admin'])->id);

        $stubMessage = 'این صفحه در مرحله فعلی به صورت استاب آماده شده است.';

        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.courses.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.orders.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.payments.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.coupons.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.banners.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.social-links.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.media.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.roles.index'))->assertOk()->assertDontSee($stubMessage);
        $this->actingAs($admin)->get(route('admin.permissions.index'))->assertOk()->assertDontSee($stubMessage);
    }
}
