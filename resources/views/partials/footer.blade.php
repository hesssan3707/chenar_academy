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
                <a href="#">YouTube</a>
                <a href="#">Telegram</a>
                <a href="#">Instagram</a>
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

