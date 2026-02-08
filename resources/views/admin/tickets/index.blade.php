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
                                <th>شناسه</th>
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
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td class="admin-min-w-260">
                                        <div class="admin-row-title">{{ $ticket->subject }}</div>
                                    </td>
                                    <td class="admin-nowrap">{{ $ticket->user_id }}</td>
                                    <td>{{ $ticket->priority }}</td>
                                    <td>{{ $ticket->status }}</td>
                                    <td class="admin-nowrap">{{ $ticket->last_message_at?->format('Y-m-d H:i') ?? '—' }}</td>
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
