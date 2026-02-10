@extends('layouts.spa')

@section('title', $title ?? 'تیکت‌های من')

@section('content')
    <div class="container h-full py-6">
        <div class="user-panel-grid">
            @include('panel.partials.sidebar')
            
            <main class="user-content flex flex-col overflow-hidden">
                <div class="cluster mb-6" style="justify-content: space-between; align-items: center;">
                    <div>
                        <h2 class="h2 mb-2">{{ $title ?? 'تیکت‌های من' }}</h2>
                        <p class="text-muted">ارسال و پیگیری پیام‌های پشتیبانی</p>
                    </div>
                    <a href="{{ route('panel.tickets.create') }}" class="btn btn--primary">ایجاد تیکت</a>
                </div>

                @php($tickets = $tickets ?? collect())

                @if ($tickets->isEmpty())
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <p class="text-muted">هنوز تیکتی ثبت نکرده‌اید.</p>
                    </div>
                @else
                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        <div class="stack stack--md">
                            @foreach ($tickets as $ticket)
                                <a href="{{ route('panel.tickets.show', $ticket->id) }}" class="panel p-4 block bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-colors">
                                    <div class="cluster" style="justify-content: space-between; align-items: center;">
                                        <div class="stack stack--xs">
                                            <div class="font-bold text-lg">{{ $ticket->subject }}</div>
                                            <div class="text-sm text-muted">
                                                <span class="inline-block px-2 py-0.5 rounded text-xs {{ $ticket->status === 'closed' ? 'bg-gray-500/20' : 'bg-green-500/20 text-green-400' }}">
                                                    {{ $ticket->status === 'closed' ? 'بسته' : 'باز' }}
                                                </span>
                                                <span class="mx-2">•</span>
                                                <span class="{{ $ticket->priority === 'high' ? 'text-red-400' : '' }}">
                                                    {{ match($ticket->priority) { 'high' => 'فوری', 'low' => 'کم', default => 'معمولی' } }}
                                                </span>
                                                @if ($ticket->last_message_at)
                                                    <span class="mx-2">•</span>
                                                    آخرین پیام: {{ jdate($ticket->last_message_at)->ago() }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="btn btn--ghost btn--sm">مشاهده</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection
