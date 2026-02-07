<header class="site-header">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__left">
                <a class="topbar__item" href="tel:09377947853">09377947853</a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="mailto:Chenaracademy@gmail.com">Chenaracademy@gmail.com</a>
            </div>
            <div class="topbar__right">
                <a class="topbar__item" href="#">YouTube</a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="#">Telegram</a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="#">Instagram</a>
            </div>
        </div>
    </div>

    <div class="navbar">
        <div class="container navbar__inner">
            <a class="brand" href="{{ route('home') }}">
                <span class="brand__mark">چنار</span>
                <span class="brand__name">آکادمی</span>
            </a>

            <button class="nav-toggle" type="button" data-nav-toggle aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="nav" data-nav>
                <a class="nav__link is-active" href="{{ route('home') }}">خانه</a>
                <a class="nav__link" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌های آموزشی</a>
                <a class="nav__link" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوهای آموزشی</a>
                <a class="nav__link" href="{{ route('blog.index') }}">وبلاگ</a>
                <a class="nav__link" href="{{ route('about') }}">درباره چنار آکادمی</a>
                <a class="nav__link" href="{{ route('contact') }}">ارتباط با ما</a>
            </nav>

            <div class="navbar__actions">
                @auth
                    <a class="btn btn--ghost" href="{{ route('panel.dashboard') }}">پنل</a>
                    <a class="btn btn--ghost" href="{{ route('panel.profile') }}">{{ auth()->user()->first_name ?: (auth()->user()->name ?: auth()->user()->phone) }}</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn--ghost" type="submit">خروج</button>
                    </form>
                @else
                    <a class="btn btn--ghost" href="{{ route('login') }}">ورود/ثبت نام</a>
                @endauth

                <a class="btn btn--primary" href="{{ route('cart.index') }}">سبد خرید</a>
            </div>
        </div>
    </div>
</header>
