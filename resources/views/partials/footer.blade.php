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
                <a href="#">جزوه‌ها</a>
                <a href="#">ویدیوها</a>
                <a href="#">وبلاگ</a>
                <a href="#">تماس با ما</a>
            </div>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">شبکه‌های اجتماعی</h3>
            <div class="footer__links">
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="YouTube" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 9.5l6 3.5-6 3.5z" />
                        <path d="M3.6 7.2c.3-1.1 1.2-1.9 2.3-2.2C7.8 4.5 12 4.5 12 4.5s4.2 0 6.1.5c1.1.3 2 1.1 2.3 2.2.5 2 .5 4.8.5 4.8s0 2.8-.5 4.8c-.3 1.1-1.2 1.9-2.3 2.2-1.9.5-6.1.5-6.1.5s-4.2 0-6.1-.5c-1.1-.3-2-1.1-2.3-2.2-.5-2-.5-4.8-.5-4.8s0-2.8.5-4.8z" />
                    </svg>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="Telegram" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 4L11 13" />
                        <path d="M22 4l-7 19-4-10-9-3z" />
                    </svg>
                </a>
                <a href="#" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="6" width="12" height="12" rx="3" />
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M16.5 7.5h0.01" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <span>© {{ now()->year }} Chenar Academy</span>
            <span class="footer__sep">|</span>
            <span>{{ config('app.name') }}</span>
        </div>
    </div>
</footer>
