<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');

        $query = Ticket::query();
        if (is_int($scopedUserId) && $scopedUserId > 0) {
            $query->where('user_id', $scopedUserId);
        }

        $tickets = $query
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.tickets.index', [
            'title' => 'تیکت‌ها',
            'tickets' => $tickets,
        ]);
    }

    public function create(): View
    {
        return view('admin.tickets.create', [
            'title' => 'ایجاد تیکت',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $scopedUserId = $request->attributes->get('adminScopedUserId');

        $validated = $request->validate([
            'user_id' => is_int($scopedUserId) && $scopedUserId > 0
                ? ['nullable']
                : ['required', 'integer', Rule::exists('users', 'id')],
            'subject' => ['required', 'string', 'max:160'],
            'priority' => ['required', 'string', Rule::in(['low', 'normal', 'high'])],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $userId = is_int($scopedUserId) && $scopedUserId > 0 ? $scopedUserId : (int) $validated['user_id'];

        $ticket = Ticket::query()->create([
            'user_id' => $userId,
            'subject' => (string) $validated['subject'],
            'priority' => (string) $validated['priority'],
            'status' => 'open',
            'last_message_at' => now(),
            'meta' => [
                'admin_last_read_at' => now()->toDateTimeString(),
            ],
        ]);

        TicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_user_id' => $request->user()?->id,
            'body' => (string) $validated['body'],
            'meta' => [],
        ]);

        return redirect()->route('admin.tickets.show', $ticket->id);
    }

    public function show(int $ticket): View
    {
        $ticketModel = Ticket::query()->findOrFail($ticket);
        $scopedUserId = request()->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && (int) $ticketModel->user_id !== $scopedUserId) {
            abort(404);
        }

        $meta = is_array($ticketModel->meta) ? $ticketModel->meta : [];
        $meta['admin_last_read_at'] = now()->toDateTimeString();
        $ticketModel->forceFill(['meta' => $meta])->save();

        $user = User::query()->find($ticketModel->user_id);

        $messages = TicketMessage::query()
            ->where('ticket_id', $ticketModel->id)
            ->orderBy('id')
            ->get();

        return view('admin.tickets.show', [
            'title' => 'نمایش تیکت',
            'ticket' => $ticketModel,
            'ticketUser' => $user,
            'messages' => $messages,
        ]);
    }

    public function edit(int $ticket): View
    {
        return redirect()->route('admin.tickets.show', $ticket);
    }

    public function update(Request $request, int $ticket): RedirectResponse
    {
        $ticketModel = Ticket::query()->findOrFail($ticket);
        $scopedUserId = $request->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && (int) $ticketModel->user_id !== $scopedUserId) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000'],
            'close' => ['nullable'],
        ]);

        $shouldClose = $request->boolean('close');
        $body = isset($validated['body']) ? trim((string) $validated['body']) : '';

        if (! $shouldClose && $body === '') {
            return redirect()
                ->route('admin.tickets.show', $ticketModel->id)
                ->withErrors(['body' => 'متن پیام الزامی است.']);
        }

        if ($body !== '') {
            TicketMessage::query()->create([
                'ticket_id' => $ticketModel->id,
                'sender_user_id' => $request->user()?->id,
                'body' => $body,
                'meta' => [],
            ]);

            $ticketModel->forceFill([
                'last_message_at' => now(),
            ])->save();
        }

        if ($shouldClose && $ticketModel->status !== 'closed') {
            $ticketModel->forceFill([
                'status' => 'closed',
                'closed_at' => now(),
            ])->save();
        }

        $meta = is_array($ticketModel->meta) ? $ticketModel->meta : [];
        $meta['admin_last_read_at'] = now()->toDateTimeString();
        $ticketModel->forceFill(['meta' => $meta])->save();

        return redirect()->route('admin.tickets.show', $ticketModel->id);
    }

    public function destroy(int $ticket): RedirectResponse
    {
        $ticketModel = Ticket::query()->findOrFail($ticket);
        $scopedUserId = request()->attributes->get('adminScopedUserId');
        if (is_int($scopedUserId) && $scopedUserId > 0 && (int) $ticketModel->user_id !== $scopedUserId) {
            abort(404);
        }

        $ticketModel->delete();

        return redirect()->route('admin.tickets.index');
    }
}
