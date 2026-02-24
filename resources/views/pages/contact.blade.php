@extends('layouts.spa')

@section('title', 'ارتباط با ما')

@section('content')
    <div class="container spa-page-shell" style="padding: 24px 0;">
        <header style="text-align: center; margin-bottom: 18px;">
            <h1 class="page-title">ارتباط با ما</h1>
            <p class="page-subtitle">مشتاق شنیدن نظرات و پیشنهادات شما هستیم</p>
        </header>

        <div class="spa-page-scroll">
            <div class="stack" style="gap: 18px;">
                <div class="grid spa-grid--3">
                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--ds-muted); margin-bottom: 2px;">تماس مستقیم</div>
                            <a href="tel:09377947853" style="font-weight: 900; font-size: 14px; color: inherit; text-decoration: none;">09377947853</a>
                        </div>
                    </div>

                    <div class="panel" style="display: flex; align-items: center; gap: 14px;">
                        <div class="spa-icon-tile">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: var(--ds-muted); margin-bottom: 2px;">آدرس دفتر</div>
                            <div style="font-size: 14px; font-weight: 900;">تهران، خیابان انقلاب</div>
                        </div>
                    </div>

                    <div class="panel" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                        <div style="font-size: 12px; color: var(--ds-muted); font-weight: 900;">شبکه‌های اجتماعی</div>
                        <div class="cluster" style="gap: 8px;">
                            @php($links = ($socialLinks ?? collect()))
                            @foreach ($links as $link)
                                @php($platform = strtolower((string) ($link->platform ?? '')))
                                <a href="{{ $link->url }}" class="spa-social-icon" title="{{ $link->title ?: ucfirst($platform) }}" target="_blank" rel="noreferrer">
                                    @if ($platform === 'telegram')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                    @elseif ($platform === 'instagram')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                    @elseif ($platform === 'youtube')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.42a2.78 2.78 0 0 0-1.94 2C1 8.14 1 12 1 12s0 3.86.46 5.58a2.78 2.78 0 0 0 1.94 2c1.72.42 8.6.42 8.6.42s6.88 0 8.6-.42a2.78 2.78 0 0 0 1.94-2C23 15.86 23 12 23 12s0-3.86-.46-5.58z"></path><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"></polygon></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 14a5 5 0 0 1 0-7l.7-.7a5 5 0 0 1 7.1 7.1l-.7.7"></path><path d="M14 10a5 5 0 0 1 0 7l-.7.7a5 5 0 0 1-7.1-7.1l.7-.7"></path></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="panel" style="padding: 22px;">
                    <form method="post" action="{{ route('contact.submit') }}" class="grid spa-grid--2" style="gap: 18px;">
                        @csrf
                        <label class="field">
                            <span class="field__label">نام و نام خانوادگی</span>
                            <input name="name" type="text" placeholder="مثلا: علی علوی" required>
                        </label>
                        <label class="field">
                            <span class="field__label">شماره تماس یا ایمیل</span>
                            <input name="contact" type="text" placeholder="0912... یا email@example.com" required>
                        </label>
                        <label class="field spa-grid-span-all">
                            <span class="field__label">پیام شما</span>
                            <textarea name="message" rows="4" placeholder="متن پیام خود را اینجا بنویسید..." required></textarea>
                        </label>
                        <div class="spa-grid-span-all" style="display: flex; justify-content: center;">
                            <button type="submit" class="btn btn--primary">ارسال پیام</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
