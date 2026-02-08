<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $tickets = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return view('panel.tickets.index', [
            'title' => 'تیکت‌های من',
            'tickets' => $tickets,
        ]);
    }

    public function create(): View
    {
        $categories = Category::query()
            ->where('type', 'ticket')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('panel.tickets.create', [
            'title' => 'ایجاد تیکت',
            'ticketCategories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'category' => [
                'required',
                'string',
                Rule::exists('categories', 'slug')
                    ->where('type', 'ticket')
                    ->where('is_active', true),
            ],
            'priority' => ['required', 'string', 'in:low,normal,high'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $category = Category::query()
            ->where('type', 'ticket')
            ->where('slug', (string) $validated['category'])
            ->where('is_active', true)
            ->first();

        if (! $category) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['category' => 'دسته‌بندی انتخاب‌شده معتبر نیست.']);
        }

        $ticket = Ticket::query()->create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'priority' => $validated['priority'],
            'status' => 'open',
            'last_message_at' => now(),
            'meta' => [
                'category_slug' => (string) $category->slug,
                'category_title' => (string) $category->title,
            ],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()->route('panel.tickets.show', $ticket->id);
    }

    public function show(Request $request, int $ticket): View
    {
        $ticketModel = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($ticket);

        $messages = TicketMessage::query()
            ->where('ticket_id', $ticketModel->id)
            ->orderBy('id')
            ->get();

        return view('panel.tickets.show', [
            'title' => 'نمایش تیکت',
            'ticket' => $ticketModel,
            'messages' => $messages,
        ]);
    }

    public function edit(int $ticket): View
    {
        abort(404);
    }

    public function update(Request $request, int $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $ticketModel = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($ticket);

        abort_if($ticketModel->status === 'closed', 403);

        TicketMessage::query()->create([
            'ticket_id' => $ticketModel->id,
            'sender_user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $ticketModel->forceFill([
            'last_message_at' => now(),
        ])->save();

        return redirect()->route('panel.tickets.show', $ticketModel->id);
    }

    public function destroy(Request $request, int $ticket): RedirectResponse
    {
        $ticketModel = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($ticket);

        abort_if($ticketModel->status === 'closed', 403);

        $ticketModel->delete();

        return redirect()->route('panel.tickets.index');
    }
}
