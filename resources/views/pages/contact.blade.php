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
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10 9.5l6 3.5-6 3.5z" />
                                        <path d="M3.6 7.2c.3-1.1 1.2-1.9 2.3-2.2C7.8 4.5 12 4.5 12 4.5s4.2 0 6.1.5c1.1.3 2 1.1 2.3 2.2.5 2 .5 4.8.5 4.8s0 2.8-.5 4.8c-.3 1.1-1.2 1.9-2.3 2.2-1.9.5-6.1.5-6.1.5s-4.2 0-6.1-.5c-1.1-.3-2-1.1-2.3-2.2-.5-2-.5-4.8-.5-4.8s0-2.8.5-4.8z" />
                                    </svg>
                                </a>
                                <a class="link" href="https://t.me" target="_blank" rel="noopener noreferrer" aria-label="Telegram" style="display:inline-flex;align-items:center;">
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 4L11 13" />
                                        <path d="M22 4l-7 19-4-10-9-3z" />
                                    </svg>
                                </a>
                                <a class="link" href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="display:inline-flex;align-items:center;">
                                    <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="6" y="6" width="12" height="12" rx="3" />
                                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                        <path d="M16.5 7.5h0.01" />
                                    </svg>
                                </a>
                            @else
                                @foreach ($links as $link)
                                    @php($label = $link->title ?: $link->platform)
                                    <a class="link" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}" style="display:inline-flex;align-items:center;">
                                        @switch(strtolower((string) $link->platform))
                                            @case('youtube')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M10 9.5l6 3.5-6 3.5z" />
                                                    <path d="M3.6 7.2c.3-1.1 1.2-1.9 2.3-2.2C7.8 4.5 12 4.5 12 4.5s4.2 0 6.1.5c1.1.3 2 1.1 2.3 2.2.5 2 .5 4.8.5 4.8s0 2.8-.5 4.8c-.3 1.1-1.2 1.9-2.3 2.2-1.9.5-6.1.5-6.1.5s-4.2 0-6.1-.5c-1.1-.3-2-1.1-2.3-2.2-.5-2-.5-4.8-.5-4.8s0-2.8.5-4.8z" />
                                                </svg>
                                                @break
                                            @case('telegram')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M22 4L11 13" />
                                                    <path d="M22 4l-7 19-4-10-9-3z" />
                                                </svg>
                                                @break
                                            @case('instagram')
                                                <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="6" y="6" width="12" height="12" rx="3" />
                                                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M16.5 7.5h0.01" />
                                                </svg>
                                                @break
                                            @default
                                                <span>{{ $label }}</span>
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
