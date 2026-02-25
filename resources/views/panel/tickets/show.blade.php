@extends('layouts.spa')

@section('title', $title ?? 'نمایش تیکت')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main flex flex-col panel-ticket" data-panel-main>
                <div class="cluster" style="justify-content: space-between; align-items: center;">
                    <div>
                        <h1 class="page-title">{{ $ticket->subject }}</h1>
                        <p class="page-subtitle">
                            @if ($ticket->status === 'closed')
                                <span style="color: #ef4444;">بسته</span>
                            @else
                                <span style="color: #22c55e;">باز</span>
                            @endif
                            <span class="footer__sep">|</span>
                            @php($categoryTitle = ($ticket->meta ?? [])['category_title'] ?? null)
                            @if ($categoryTitle)
                                {{ $categoryTitle }}
                                <span class="footer__sep">|</span>
                            @endif
                            @if ($ticket->priority === 'high')
                                فوری
                            @elseif ($ticket->priority === 'low')
                                کم
                            @else
                                معمولی
                            @endif
                        </p>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('panel.tickets.index') }}">بازگشت</a>
                </div>

                @php($messages = $messages ?? collect())

                {{-- Messenger-style Chat Container --}}
                <div class="ticket-chat" id="ticket-chat-container">
                    <div class="ticket-chat__messages">
                        @forelse ($messages as $message)
                            @php($isUser = (int) ($message->sender_user_id ?? 0) === (int) auth()->id())
                            @php($hasAttachment = !empty(($message->meta ?? [])['attachment_url']))
                            <div class="ticket-bubble {{ $isUser ? 'ticket-bubble--user' : 'ticket-bubble--admin' }}">
                                <div class="ticket-bubble__sender">{{ $isUser ? 'شما' : 'پشتیبانی' }}</div>
                                <div class="ticket-bubble__body">
                                    <span style="white-space: pre-wrap;">{{ $message->body }}</span>
                                    @if ($hasAttachment)
                                        <a href="{{ ($message->meta ?? [])['attachment_url'] }}" target="_blank" rel="noopener" class="ticket-bubble__attachment" title="فایل پیوست">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                            </svg>
                                            <span>پیوست</span>
                                        </a>
                                    @endif
                                </div>
                                @if ($message->created_at)
                                    <div class="ticket-bubble__time">{{ jdate($message->created_at)->ago() }}</div>
                                @endif
                            </div>
                        @empty
                            <div style="text-align: center; padding: 40px 0; color: rgba(255,255,255,0.4);">هنوز پیامی ارسال نشده است.</div>
                        @endforelse
                    </div>
                </div>

                {{-- Message Form & Actions --}}
                @if ($ticket->status !== 'closed')
                    <div class="ticket-form" style="margin-top: 12px;">
                        <form method="post" action="{{ route('panel.tickets.update', $ticket->id) }}" class="ticket-composer" enctype="multipart/form-data">
                            @csrf
                            @method('put')

                            <label class="field" style="margin: 0;">
                                <textarea name="body" required placeholder="پیام خود را بنویسید..." rows="3" style="resize: vertical;">{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field" style="margin: 0;">
                                <span class="field__label" style="font-size: 12px; opacity: 0.7;">پیوست تصویر (حداکثر ۱ مگابایت)</span>
                                <input type="file" name="attachment" accept="image/*">
                                @error('attachment')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="ticket-actions-row">
                                <button class="btn btn--primary" type="submit">ارسال پیام</button>

                                <button class="btn btn--ghost" type="submit" formaction="{{ route('panel.tickets.close', $ticket->id) }}" formmethod="post"
                                    onclick="return confirm('آیا از بستن این تیکت مطمئن هستید؟')">بستن تیکت</button>

                                <form method="post" action="{{ route('panel.tickets.destroy', $ticket->id) }}" style="display: inline;"
                                    onsubmit="return confirm('آیا از حذف این تیکت مطمئن هستید؟ این عملیات قابل بازگشت نیست.')">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn--danger btn--sm" type="submit">حذف تیکت</button>
                                </form>
                            </div>
                        </form>
                    </div>
                @else
                    <div style="margin-top: 12px; text-align: center; padding: 12px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); color: rgba(255,255,255,0.5);">
                        این تیکت بسته شده است.
                    </div>
                @endif
            </main>
        </div>
    </div>

    <style>
        .ticket-chat {
            height: 420px;
            overflow-y: auto;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 16px;
            margin-top: 16px;
            scroll-behavior: smooth;
        }
        .ticket-chat__messages {
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 100%;
            justify-content: flex-end;
        }
        .ticket-bubble {
            max-width: 75%;
            padding: 10px 14px;
            border-radius: 16px;
            position: relative;
        }
        .ticket-bubble--user {
            align-self: flex-start;
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-bottom-left-radius: 4px;
        }
        .ticket-bubble--admin {
            align-self: flex-end;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-bottom-right-radius: 4px;
        }
        .ticket-bubble__sender {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 4px;
            opacity: 0.7;
        }
        .ticket-bubble--user .ticket-bubble__sender {
            color: #60a5fa;
        }
        .ticket-bubble--admin .ticket-bubble__sender {
            color: #34d399;
        }
        .ticket-bubble__body {
            font-size: 14px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }
        .ticket-bubble__attachment {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            font-size: 12px;
            color: #60a5fa;
            text-decoration: none;
            opacity: 0.8;
        }
        .ticket-bubble__attachment:hover {
            opacity: 1;
            text-decoration: underline;
        }
        .ticket-bubble__time {
            font-size: 10px;
            margin-top: 6px;
            opacity: 0.5;
            text-align: left;
        }
        .ticket-bubble--admin .ticket-bubble__time {
            text-align: right;
        }
        .ticket-composer {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .ticket-actions-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        @media (max-width: 640px) {
            .ticket-bubble {
                max-width: 90%;
            }
            .ticket-chat {
                height: 350px;
            }
        }
    </style>

    <script>
        (function () {
            var chat = document.getElementById('ticket-chat-container');
            if (chat) chat.scrollTop = chat.scrollHeight;
        })();
    </script>
@endsection
