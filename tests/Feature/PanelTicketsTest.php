<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelTicketsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_ticket_and_see_it_in_list(): void
    {
        $user = User::factory()->create();

        $category = Category::query()->create([
            'type' => 'ticket',
            'parent_id' => null,
            'title' => 'پشتیبانی فنی',
            'slug' => 'technical',
            'icon_key' => null,
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->post(route('panel.tickets.store'), [
                'subject' => 'مشکل ورود',
                'category' => $category->slug,
                'priority' => 'normal',
                'body' => 'سلام، نمی‌توانم وارد حساب کاربری شوم.',
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('مشکل ورود', $ticket->subject);
        $this->assertSame('technical', (string) (($ticket->meta ?? [])['category_slug'] ?? ''));

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'sender_user_id' => $user->id,
            'body' => 'سلام، نمی‌توانم وارد حساب کاربری شوم.',
        ]);

        $this->actingAs($user)
            ->get(route('panel.tickets.index'))
            ->assertOk()
            ->assertSee('مشکل ورود');
    }

    public function test_user_cannot_view_other_users_ticket(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ticket = Ticket::query()->create([
            'user_id' => $owner->id,
            'subject' => 'تست',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => null,
            'meta' => [],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $owner->id,
            'body' => 'پیام',
            'meta' => [],
        ]);

        $this->actingAs($otherUser)
            ->get(route('panel.tickets.show', $ticket->id))
            ->assertNotFound();
    }

    public function test_user_can_reply_to_own_open_ticket(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'subject' => 'تست پاسخ',
            'status' => 'open',
            'priority' => 'normal',
            'last_message_at' => now()->subMinute(),
            'closed_at' => null,
            'meta' => [],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $user->id,
            'body' => 'پیام اول',
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->put(route('panel.tickets.update', $ticket->id), [
                'body' => 'پیام دوم',
            ])
            ->assertRedirect(route('panel.tickets.show', $ticket->id));

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'sender_user_id' => $user->id,
            'body' => 'پیام دوم',
        ]);

        $this->actingAs($user)
            ->get(route('panel.tickets.show', $ticket->id))
            ->assertOk()
            ->assertSee('پیام اول')
            ->assertSee('پیام دوم');
    }

    public function test_user_cannot_reply_to_closed_ticket(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::query()->create([
            'user_id' => $user->id,
            'subject' => 'تست بسته',
            'status' => 'closed',
            'priority' => 'normal',
            'last_message_at' => now(),
            'closed_at' => now(),
            'meta' => [],
        ]);

        $this->actingAs($user)
            ->put(route('panel.tickets.update', $ticket->id), [
                'body' => 'پیام',
            ])
            ->assertForbidden();
    }
}
