<footer class="site-footer">
    <div class="container footer__inner">
        <div class="footer__col">
            <div class="footer__brand">
                <span class="brand__mark">چنار</span>
                <span class="brand__name">آکادمی</span>
            </div>
            <p class="footer__text">
                فرصتی برای یادگیری و پیشرفت؛ آموزش، جزوه، ویدیو و وبینارهای آموزشی.
            </p>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">دسترسی سریع</h3>
            <div class="footer__links">
                <a href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                <a href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                <a href="{{ route('blog.index') }}">وبلاگ</a>
                <a href="{{ route('contact') }}">تماس با ما</a>
            </div>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">شبکه‌های اجتماعی</h3>
            <div class="footer__links">
                @php($links = ($socialLinks ?? collect()))
                @foreach ($links as $link)
                    @php($platform = strtolower((string) $link->platform))
                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link->title ?: ucfirst($platform) }}" style="display:inline-flex;align-items:center;">
                        @if ($platform === 'youtube')
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 15.5v-7l6 3.5-6 3.5Z" />
                                <path fill-rule="evenodd" d="M3.55 7.35c.26-1.01 1.06-1.81 2.07-2.07C7.45 4.8 12 4.8 12 4.8s4.55 0 6.38.48c1.01.26 1.81 1.06 2.07 2.07.47 1.84.47 4.65.47 4.65s0 2.81-.47 4.65c-.26 1.01-1.06 1.81-2.07 2.07-1.83.48-6.38.48-6.38.48s-4.55 0-6.38-.48c-1.01-.26-1.81-1.06-2.07-2.07-.47-1.84-.47-4.65-.47-4.65s0-2.81.47-4.65Z" clip-rule="evenodd" />
                            </svg>
                        @elseif ($platform === 'telegram')
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" />
                            </svg>
                        @elseif ($platform === 'instagram')
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="6" y="6" width="12" height="12" rx="3" />
                                <path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" />
                                <path d="M16.2 7.8h.01" />
                            </svg>
                        @else
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 14a5 5 0 0 1 0-7l.7-.7a5 5 0 0 1 7.1 7.1l-.7.7" />
                                <path d="M14 10a5 5 0 0 1 0 7l-.7.7a5 5 0 0 1-7.1-7.1l.7-.7" />
                            </svg>
                        @endif
                    </a>
                @endforeach

                @if ($links->isEmpty())
                    <div class="cluster" style="gap: 12px;">
                        <span aria-label="اینستاگرام" style="display:inline-flex;align-items:center;">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="6" y="6" width="12" height="12" rx="3" />
                                <path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" />
                                <path d="M16.2 7.8h.01" />
                            </svg>
                        </span>
                        <span aria-label="تلگرام" style="display:inline-flex;align-items:center;">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" />
                            </svg>
                        </span>
                        <span aria-label="یوتیوب" style="display:inline-flex;align-items:center;">
                            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M10 15.5v-7l6 3.5-6 3.5Z" />
                                <path fill-rule="evenodd" d="M3.55 7.35c.26-1.01 1.06-1.81 2.07-2.07C7.45 4.8 12 4.8 12 4.8s4.55 0 6.38.48c1.01.26 1.81 1.06 2.07 2.07.47 1.84.47 4.65.47 4.65s0 2.81-.47 4.65c-.26 1.01-1.06 1.81-2.07 2.07-1.83.48-6.38.48-6.38.48s-4.55 0-6.38-.48c-1.01-.26-1.81-1.06-2.07-2.07-.47-1.84-.47-4.65-.47-4.65s0-2.81.47-4.65Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <span>© {{ now()->year }} آکادمی چنار - همه حقوق محفوظ است</span>
            <span class="footer__sep">|</span>
            <span>{{ config('app.name') ?: 'چنار' }}</span>
        </div>
    </div>
</footer>
