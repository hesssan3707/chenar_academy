<header class="site-header">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__left">
                <a class="topbar__item" href="tel:09377947853">09377947853</a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="mailto:Chenaracademy@gmail.com">Chenaracademy@gmail.com</a>
            </div>
            <div class="topbar__right">
                <a class="topbar__item" href="#" target="_blank" rel="noopener noreferrer" aria-label="YouTube" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 9.5l6 3.5-6 3.5z" />
                        <path d="M3.6 7.2c.3-1.1 1.2-1.9 2.3-2.2C7.8 4.5 12 4.5 12 4.5s4.2 0 6.1.5c1.1.3 2 1.1 2.3 2.2.5 2 .5 4.8.5 4.8s0 2.8-.5 4.8c-.3 1.1-1.2 1.9-2.3 2.2-1.9.5-6.1.5-6.1.5s-4.2 0-6.1-.5c-1.1-.3-2-1.1-2.3-2.2-.5-2-.5-4.8-.5-4.8s0-2.8.5-4.8z" />
                    </svg>
                </a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="#" target="_blank" rel="noopener noreferrer" aria-label="Telegram" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 4L11 13" />
                        <path d="M22 4l-7 19-4-10-9-3z" />
                    </svg>
                </a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="#" target="_blank" rel="noopener noreferrer" aria-label="Instagram" style="display:inline-flex;align-items:center;">
                    <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="6" width="12" height="12" rx="3" />
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M16.5 7.5h0.01" />
                    </svg>
                </a>
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
                    <a class="btn btn--ghost" href="{{ route('panel.profile') }}">{{ auth()->user()->name ?: auth()->user()->phone }}</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn--ghost" type="submit">خروج</button>
                    </form>
                @else
                    <a class="btn btn--ghost" href="{{ route('login') }}">ورود/ثبت نام</a>
                @endauth

                <a class="btn btn--primary" href="{{ route('cart.index') }}">
                    سبد خرید
                    @if (($cartItemCount ?? 0) > 0)
                        <span class="badge badge--brand">{{ $cartItemCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>
