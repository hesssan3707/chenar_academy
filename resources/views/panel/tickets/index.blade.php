@extends('layouts.app')

@section('title', $title ?? 'تیکت‌های من')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <div class="cluster" style="justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">{{ $title ?? 'تیکت‌های من' }}</h1>
                    <p class="page-subtitle">ارسال و پیگیری پیام‌های پشتیبانی</p>
                </div>
                <a class="btn btn--primary" href="{{ route('panel.tickets.create') }}">ایجاد تیکت</a>
            </div>

            @php($tickets = $tickets ?? collect())

            @if ($tickets->isEmpty())
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle" style="margin: 0;">هنوز تیکتی ثبت نکرده‌اید.</p>
                </div>
            @else
                <div class="stack" style="margin-top: 18px;">
                    @foreach ($tickets as $ticket)
                        <a class="panel" href="{{ route('panel.tickets.show', $ticket->id) }}">
                            <div class="cluster" style="justify-content: space-between; align-items: center;">
                                <div class="stack stack--sm">
                                    <div class="field__label">{{ $ticket->subject }}</div>
                                    <div class="card__meta">
                                        @if ($ticket->status === 'closed')
                                            بسته
                                        @else
                                            باز
                                        @endif
                                        <span class="footer__sep">|</span>
                                        @if ($ticket->priority === 'high')
                                            فوری
                                        @elseif ($ticket->priority === 'low')
                                            کم
                                        @else
                                            معمولی
                                        @endif
                                        @if ($ticket->last_message_at)
                                            <span class="footer__sep">|</span>
                                            آخرین پیام: {{ $ticket->last_message_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                                <div class="btn btn--ghost btn--sm">مشاهده</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
