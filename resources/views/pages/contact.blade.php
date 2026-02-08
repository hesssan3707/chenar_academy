@extends('layouts.app')

@section('title', 'ارتباط با ما')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">ارتباط با ما</h1>
            <p class="page-subtitle">راه‌های ارتباطی و ارسال پیام</p>

            <div class="panel max-w-md" style="margin-bottom: 18px;">
                <div class="stack stack--sm">
                    <div class="cluster" style="justify-content: space-between;">
                        <div>
                            <div class="field__label">شماره تماس</div>
                            <a class="link" href="tel:09377947853">09377947853</a>
                        </div>
                        <div>
                            <div class="field__label">ساعات کاری</div>
                            <div>شنبه تا پنجشنبه، ۹ تا ۱۸</div>
                        </div>
                    </div>

                    <div>
                        <div class="field__label">شبکه‌های اجتماعی</div>
                        <div class="cluster">
                            @php
                                $links = ($socialLinks ?? collect())->where('url', '!=', '');
                            @endphp

                            @if ($links->isEmpty())
                                <a class="link" href="https://www.youtube.com" target="_blank" rel="noopener noreferrer" aria-label="YouTube" style="display:inline-flex;align-items:center;">
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M10 15.5v-7l6 3.5-6 3.5Z" />
                                        <path fill-rule="evenodd" d="M3.55 7.35c.26-1.01 1.06-1.81 2.07-2.07C7.45 4.8 12 4.8 12 4.8s4.55 0 6.38.48c1.01.26 1.81 1.06 2.07 2.07.47 1.84.47 4.65.47 4.65s0 2.81-.47 4.65c-.26 1.01-1.06 1.81-2.07 2.07-1.83.48-6.38.48-6.38.48s-4.55 0-6.38-.48c-1.01-.26-1.81-1.06-2.07-2.07-.47-1.84-.47-4.65-.47-4.65s0-2.81.47-4.65Z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a class="link" href="https://t.me" target="_blank" rel="noopener noreferrer" aria-label="Telegram" style="display:inline-flex;align-items:center;">
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" />
                                    </svg>
                                </a>
                                <a class="link" href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="display:inline-flex;align-items:center;">
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="6" y="6" width="12" height="12" rx="3" />
                                        <path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" />
                                        <path d="M16.2 7.8h.01" />
                                    </svg>
                                </a>
                            @else
                                @foreach ($links as $link)
                                    @php($label = $link->title ?: $link->platform)
                                    <a class="link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}" style="display:inline-flex;align-items:center;">
                                        @switch(strtolower((string) $link->platform))
                                            @case('youtube')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M10 15.5v-7l6 3.5-6 3.5Z" />
                                                    <path fill-rule="evenodd" d="M3.55 7.35c.26-1.01 1.06-1.81 2.07-2.07C7.45 4.8 12 4.8 12 4.8s4.55 0 6.38.48c1.01.26 1.81 1.06 2.07 2.07.47 1.84.47 4.65.47 4.65s0 2.81-.47 4.65c-.26 1.01-1.06 1.81-2.07 2.07-1.83.48-6.38.48-6.38.48s-4.55 0-6.38-.48c-1.01-.26-1.81-1.06-2.07-2.07-.47-1.84-.47-4.65-.47-4.65s0-2.81.47-4.65Z" clip-rule="evenodd" />
                                                </svg>
                                                @break
                                            @case('telegram')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" />
                                                </svg>
                                                @break
                                            @case('instagram')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="6" y="6" width="12" height="12" rx="3" />
                                                    <path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" />
                                                    <path d="M16.2 7.8h.01" />
                                                </svg>
                                                @break
                                            @default
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M10 14a5 5 0 0 1 0-7l.7-.7a5 5 0 0 1 7.1 7.1l-.7.7" />
                                                    <path d="M14 10a5 5 0 0 1 0 7l-.7.7a5 5 0 0 1-7.1-7.1l.7-.7" />
                                                </svg>
                                        @endswitch
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel max-w-md">
                <form method="post" action="{{ route('contact.submit') }}" class="stack stack--sm">
                    @csrf

                    <label class="field">
                        <span class="field__label">نام</span>
                        <input name="name" type="text" autocomplete="name" required>
                    </label>

                    <label class="field">
                        <span class="field__label">ایمیل</span>
                        <input name="email" type="email" autocomplete="email">
                    </label>

                    <label class="field">
                        <span class="field__label">پیام</span>
                        <textarea name="message" rows="5" required></textarea>
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ارسال پیام</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
