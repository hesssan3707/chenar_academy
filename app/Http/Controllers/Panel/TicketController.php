<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
            ->ofType('ticket')
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
                    ->where('category_type_id', Category::typeId('ticket'))
                    ->where('is_active', true),
            ],
            'priority' => ['required', 'string', 'in:low,normal,high'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $category = Category::query()
            ->ofType('ticket')
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
            'attachment' => ['nullable', 'file', 'image', 'max:1024'],
        ]);

        $ticketModel = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($ticket);

        abort_if($ticketModel->status === 'closed', 403);

        $meta = [];
        $attachment = $request->file('attachment');
        if ($attachment instanceof UploadedFile) {
            $media = $this->storeUploadedMedia($attachment, 'public', 'uploads/ticket-attachments');
            if ($media) {
                $meta['attachment_media_id'] = $media->id;
                $meta['attachment_url'] = \Storage::disk('public')->url($media->path);
            }
        }

        TicketMessage::query()->create([
            'ticket_id' => $ticketModel->id,
            'sender_user_id' => $request->user()->id,
            'body' => $validated['body'],
            'meta' => $meta ?: null,
        ]);

        $ticketModel->forceFill([
            'last_message_at' => now(),
        ])->save();

        return redirect()->route('panel.tickets.show', $ticketModel->id);
    }

    public function close(Request $request, int $ticket): RedirectResponse
    {
        $ticketModel = Ticket::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($ticket);

        abort_if($ticketModel->status === 'closed', 403);

        $ticketModel->forceFill([
            'status' => 'closed',
            'closed_at' => now(),
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

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): ?Media
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($directory, $disk);

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }
}
