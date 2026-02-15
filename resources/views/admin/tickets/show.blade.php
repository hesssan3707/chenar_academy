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

            <div class="panel">
                <div class="stack stack--sm">
                    <div class="field__label">پیام‌ها</div>

                    @if (! $messages || $messages->isEmpty())
                        <div class="card__meta">هنوز پیامی ثبت نشده است.</div>
                    @else
                        <div class="ticket-thread">
                            @foreach ($messages as $ticketMessage)
                                @php($isUserMessage = (int) ($ticketMessage->sender_user_id ?? 0) === (int) $ticket->user_id)
                                @php($sender = $ticketMessage->sender ?? null)
                                @php($senderName = trim((string) ($sender?->first_name ?? '').' '.(string) ($sender?->last_name ?? '')))
                                @php($senderName = $senderName !== '' ? $senderName : (string) ($sender?->name ?? ''))
                                @php($authorName = $isUserMessage ? ($ticketUserName !== '' ? $ticketUserName : 'کاربر') : ($senderName !== '' ? $senderName : 'ادمین'))
                                <div class="ticket-msg @if (! $isUserMessage) ticket-msg--admin @else ticket-msg--user @endif">
                                    <div class="ticket-msg__meta">
                                        <div class="ticket-msg__author">{{ $authorName }}</div>
                                        <div class="ticket-msg__time">{{ $ticketMessage->created_at ? jdate($ticketMessage->created_at)->format('Y/m/d H:i') : '—' }}</div>
                                    </div>
                                    <div class="ticket-msg__body">{{ $ticketMessage->body }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid admin-grid-2">
                <div class="panel">
                    <form method="post" action="{{ route('admin.tickets.update', $ticket->id) }}" class="stack stack--sm">
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
                            <span class="field__label">بستن تیکت</span>
                            @php($closeValue = (string) old('close', '0'))
                            <select name="close">
                                <option value="0" @selected($closeValue === '0')>خیر</option>
                                <option value="1" @selected($closeValue === '1')>بله</option>
                            </select>
                        </label>

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ثبت</button>
                        </div>
                    </form>
                </div>

                <div class="panel">
                    <form method="post"
                        action="{{ route('admin.tickets.destroy', $ticket->id) }}"
                        class="stack stack--sm"
                        data-confirm="1"
                        data-confirm-title="حذف تیکت"
                        data-confirm-message="آیا از حذف این تیکت مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                        <div class="field__label">عملیات</div>
                        <button class="btn btn--danger" type="submit">حذف تیکت</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
