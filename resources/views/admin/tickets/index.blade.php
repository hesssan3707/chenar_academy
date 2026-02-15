@extends('layouts.admin')

@section('title', $title ?? 'تیکت‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'تیکت‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت تیکت‌های پشتیبانی</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.tickets.create') }}">ایجاد تیکت</a>
                </div>
            </div>

            @php($tickets = $tickets ?? null)

            @if (! $tickets || $tickets->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز تیکتی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>موضوع</th>
                                <th>کاربر</th>
                                <th>اولویت</th>
                                <th>وضعیت</th>
                                <th>آخرین پیام</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tickets as $ticket)
                                @php($priorityLabel = match ((string) ($ticket->priority ?? '')) {
                                    'low' => 'کم',
                                    'normal' => 'معمولی',
                                    'high' => 'بالا',
                                    default => (string) ($ticket->priority ?? '—'),
                                })
                                @php($statusLabel = match ((string) ($ticket->status ?? '')) {
                                    'open' => 'باز',
                                    'closed' => 'بسته',
                                    default => (string) ($ticket->status ?? '—'),
                                })
                                <tr>
                                    <td class="admin-min-w-260">
                                        <div class="admin-row-title">{{ $ticket->subject }}</div>
                                    </td>
                                    @php($user = $ticket->user ?? null)
                                    @php($displayName = trim((string) ($user?->first_name ?? '').' '.(string) ($user?->last_name ?? '')))
                                    @php($displayName = $displayName !== '' ? $displayName : (string) ($user?->name ?? ''))
                                    <td class="admin-nowrap">
                                        {{ $displayName !== '' ? $displayName : '—' }}
                                        @if ($user?->phone)
                                            <span class="text-muted" dir="ltr">{{ $user->phone }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $priorityLabel }}</td>
                                    <td>{{ $statusLabel }}</td>
                                    <td class="admin-nowrap">{{ $ticket->last_message_at ? jdate($ticket->last_message_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.tickets.show', $ticket->id) }}">نمایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
