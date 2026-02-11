@extends('layouts.spa')

@section('title', $title ?? 'نمایش تیکت')

@section('content')
    <div class="container h-full py-6">
        <div class="user-panel-grid">
            @include('panel.partials.sidebar')
            
            <main class="user-content flex flex-col overflow-hidden">
            <div class="cluster" style="justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="page-title">{{ $ticket->subject }}</h1>
                    <p class="page-subtitle">
                        @if ($ticket->status === 'closed')
                            بسته
                        @else
                            باز
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

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                <div class="stack stack--md" style="margin-top: 6px;">
                @foreach ($messages as $message)
                    @php($isUser = (int) ($message->sender_user_id ?? 0) === (int) auth()->id())
                    <div class="panel p-4 bg-white/5 border border-white/10 rounded-xl" style="{{ $isUser ? 'background: rgba(15,26,46,0.35); border-style: dashed;' : '' }}">
                        <div class="stack stack--sm">
                            <div class="cluster" style="justify-content: space-between;">
                                <div class="field__label">{{ $isUser ? 'شما' : 'پشتیبانی' }}</div>
                                @if ($message->created_at)
                                    <div class="card__meta">{{ jdate($message->created_at)->ago() }}</div>
                                @endif
                            </div>
                            <div style="white-space: pre-wrap;">{{ $message->body }}</div>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>

            @if ($ticket->status !== 'closed')
                <div class="panel max-w-md p-6 bg-white/5 border border-white/10 rounded-xl" style="margin-top: 18px;">
                    <form method="post" action="{{ route('panel.tickets.update', $ticket->id) }}" class="stack stack--sm">
                        @csrf
                        @method('put')

                        <label class="field">
                            <span class="field__label">ارسال پیام</span>
                            <textarea name="body" required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ارسال</button>
                        </div>
                    </form>
                </div>
            @endif
            </main>
        </div>
    </div>
@endsection
