@extends('layouts.admin')

@section('title', $title ?? 'نمایش تیکت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نمایش تیکت' }}</h1>
                    <p class="page-subtitle">مدیریت پیام‌ها و وضعیت تیکت</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.tickets.index') }}">بازگشت</a>
                </div>
            </div>

            @php($ticket = $ticket ?? null)
            @php($ticketUser = $ticketUser ?? null)
            @php($messages = $messages ?? collect())
            @php($statusLabel = match ((string) ($ticket->status ?? '')) {
                'open' => 'باز',
                'closed' => 'بسته',
                default => (string) ($ticket->status ?? '—'),
            })
            @php($priorityLabel = match ((string) ($ticket->priority ?? '')) {
                'low' => 'کم',
                'normal' => 'معمولی',
                'high' => 'بالا',
                default => (string) ($ticket->priority ?? '—'),
            })
            @php($ticketUserName = trim((string) ($ticketUser?->first_name ?? '').' '.(string) ($ticketUser?->last_name ?? '')))
            @php($ticketUserName = $ticketUserName !== '' ? $ticketUserName : (string) ($ticketUser?->name ?? ''))
            @php($ticketUserName = $ticketUserName !== '' ? $ticketUserName : (string) ($ticketUser?->phone ?? ''))

            <div class="grid admin-grid-2">
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="card__meta">موضوع</div>
                        <div class="admin-row-title">{{ $ticket->subject }}</div>
                        <div class="card__meta">کاربر: {{ $ticketUserName !== '' ? $ticketUserName : ($ticket->user_id ?? '—') }}</div>
                    </div>
                </div>
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="admin-kv">
                            <div class="card__meta">وضعیت</div>
                            <div class="admin-kv__value">{{ $statusLabel }}</div>
                        </div>
                        <div class="admin-kv">
                            <div class="card__meta">اولویت</div>
                            <div class="admin-kv__value">{{ $priorityLabel }}</div>
                        </div>
                        <div class="admin-kv">
                            <div class="card__meta">آخرین پیام</div>
                            <div class="admin-kv__value">{{ $ticket->last_message_at ? jdate($ticket->last_message_at)->format('Y/m/d H:i') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel" style="padding: 0; overflow: hidden;">
                <div style="padding: 12px 16px; border-bottom: 1px solid rgba(0,0,0,0.08); font-weight: 600; font-size: 14px;">پیام‌ها</div>
                <div class="admin-ticket-chat" id="admin-ticket-chat">
                    <div class="admin-ticket-chat__messages">
                        @if (! $messages || $messages->isEmpty())
                            <div style="text-align: center; padding: 40px 0; color: #999;">هنوز پیامی ثبت نشده است.</div>
                        @else
                            @foreach ($messages as $ticketMessage)
                                @php($isUserMessage = (int) ($ticketMessage->sender_user_id ?? 0) === (int) $ticket->user_id)
                                @php($sender = $ticketMessage->sender ?? null)
                                @php($senderName = trim((string) ($sender?->first_name ?? '').' '.(string) ($sender?->last_name ?? '')))
                                @php($senderName = $senderName !== '' ? $senderName : (string) ($sender?->name ?? ''))
                                @php($authorName = $isUserMessage ? ($ticketUserName !== '' ? $ticketUserName : 'کاربر') : ($senderName !== '' ? $senderName : 'ادمین'))
                                @php($hasAttachment = !empty(($ticketMessage->meta ?? [])['attachment_url']))
                                <div class="admin-chat-bubble {{ $isUserMessage ? 'admin-chat-bubble--user' : 'admin-chat-bubble--admin' }}">
                                    <div class="admin-chat-bubble__sender">{{ $authorName }}</div>
                                    <div class="admin-chat-bubble__body">
                                        <span style="white-space: pre-wrap;">{{ $ticketMessage->body }}</span>
                                        @if ($hasAttachment)
                                            <a href="{{ ($ticketMessage->meta ?? [])['attachment_url'] }}" target="_blank" rel="noopener" class="admin-chat-bubble__attachment">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                                </svg>
                                                پیوست
                                            </a>
                                        @endif
                                    </div>
                                    <div class="admin-chat-bubble__time">{{ $ticketMessage->created_at ? jdate($ticketMessage->created_at)->format('Y/m/d H:i') : '—' }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="panel">
                <form method="post" action="{{ route('admin.tickets.update', $ticket->id) }}" class="stack stack--sm" enctype="multipart/form-data" id="admin-ticket-reply-form">
                    @csrf
                    @method('put')

                    <label class="field">
                        <span class="field__label">پاسخ</span>
                        <textarea name="body">{{ old('body') }}</textarea>
                        @error('body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">پیوست تصویر (حداکثر ۱ مگابایت)</span>
                        <input type="file" name="attachment" accept="image/*">
                        @error('attachment')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions" style="gap: 8px; flex-wrap: wrap;">
                        <button class="btn btn--primary" type="submit">ارسال پیام</button>

                        @if ($ticket->status !== 'closed')
                            <label class="field" style="margin: 0; display: inline-flex; align-items: center; gap: 6px;">
                                <input type="checkbox" name="close" value="1">
                                <span style="font-size: 13px;">بستن تیکت</span>
                            </label>
                        @endif

                        <button class="btn btn--danger btn--sm" type="submit" form="admin-ticket-delete-form">حذف تیکت</button>
                    </div>
                </form>

                <form method="post"
                    action="{{ route('admin.tickets.destroy', $ticket->id) }}"
                    id="admin-ticket-delete-form"
                    style="display: none;"
                    data-confirm="1"
                    data-confirm-title="حذف تیکت"
                    data-confirm-message="آیا از حذف این تیکت مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                    @csrf
                    @method('delete')
                </form>
            </div>
        </div>
    </section>

    <style>
        .admin-ticket-chat {
            height: 450px;
            overflow-y: auto;
            padding: 16px;
            background: #fafafa;
            scroll-behavior: smooth;
        }
        .admin-ticket-chat__messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 100%;
            justify-content: flex-end;
        }
        .admin-chat-bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 14px;
            position: relative;
        }
        .admin-chat-bubble--user {
            align-self: flex-start;
            background: #e8f0fe;
            border: 1px solid #d0dff5;
            border-bottom-left-radius: 4px;
        }
        .admin-chat-bubble--admin {
            align-self: flex-end;
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-bottom-right-radius: 4px;
        }
        .admin-chat-bubble__sender {
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 3px;
        }
        .admin-chat-bubble--user .admin-chat-bubble__sender {
            color: #1d4ed8;
        }
        .admin-chat-bubble--admin .admin-chat-bubble__sender {
            color: #047857;
        }
        .admin-chat-bubble__body {
            font-size: 14px;
            line-height: 1.6;
            color: #1e293b;
        }
        .admin-chat-bubble__attachment {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 6px;
            font-size: 12px;
            color: #2563eb;
            text-decoration: none;
        }
        .admin-chat-bubble__attachment:hover {
            text-decoration: underline;
        }
        .admin-chat-bubble__time {
            font-size: 10px;
            margin-top: 4px;
            color: #94a3b8;
        }
        .admin-chat-bubble--admin .admin-chat-bubble__time {
            text-align: right;
        }
    </style>

    <script>
        (function () {
            var chat = document.getElementById('admin-ticket-chat');
            if (chat) chat.scrollTop = chat.scrollHeight;
        })();
    </script>
@endsection
