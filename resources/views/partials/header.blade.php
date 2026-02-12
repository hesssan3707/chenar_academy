<header class="site-header">
    <div class="topbar">
        <div class="container topbar__inner">
            <div class="topbar__left">
                <a class="topbar__item" href="tel:09377947853">09377947853</a>
                <span class="topbar__dot"></span>
                <a class="topbar__item" href="mailto:Chenaracademy@gmail.com">Chenaracademy@gmail.com</a>
            </div>
            <div class="topbar__right">
                @php($links = ($socialLinks ?? collect()))
                @foreach ($links as $link)
                    @php($platform = strtolower((string) $link->platform))
                    <a class="topbar__item" href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $link->title ?: ucfirst($platform) }}" style="display:inline-flex;align-items:center;">
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
                    @if (! $loop->last)
                        <span class="topbar__dot"></span>
                    @endif
                @endforeach

                @if ($links->isEmpty())
                    <span class="topbar__item" aria-label="اینستاگرام" style="display:inline-flex;align-items:center;">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="6" width="12" height="12" rx="3" />
                            <path d="M12 13.4a1.4 1.4 0 1 0 0-2.8a1.4 1.4 0 0 0 0 2.8Z" />
                            <path d="M16.2 7.8h.01" />
                        </svg>
                    </span>
                    <span class="topbar__dot"></span>
                    <span class="topbar__item" aria-label="تلگرام" style="display:inline-flex;align-items:center;">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21.9 5.24c.2-.88-.75-1.55-1.55-1.22L2.9 11.1c-.93.37-.86 1.73.1 1.98l4.78 1.23 1.83 5.5c.28.83 1.37.93 1.8.16l2.7-4.74 4.82 3.55c.72.53 1.74.12 1.92-.77l2.05-12.77ZM9.3 13.63l9.25-5.84-7.46 7.13-.28 2.84-1.46-4.38-3.21-.83Z" />
                        </svg>
                    </span>
                    <span class="topbar__dot"></span>
                    <span class="topbar__item" aria-label="یوتیوب" style="display:inline-flex;align-items:center;">
                        <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M10 15.5v-7l6 3.5-6 3.5Z" />
                            <path fill-rule="evenodd" d="M3.55 7.35c.26-1.01 1.06-1.81 2.07-2.07C7.45 4.8 12 4.8 12 4.8s4.55 0 6.38.48c1.01.26 1.81 1.06 2.07 2.07.47 1.84.47 4.65.47 4.65s0 2.81-.47 4.65c-.26 1.01-1.06 1.81-2.07 2.07-1.83.48-6.38.48-6.38.48s-4.55 0-6.38-.48c-1.01-.26-1.81-1.06-2.07-2.07-.47-1.84-.47-4.65-.47-4.65s0-2.81.47-4.65Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                @endif
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
                <a class="nav__link" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                <a class="nav__link" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                <a class="nav__link" href="{{ route('blog.index') }}">وبلاگ</a>
                <a class="nav__link" href="{{ route('about') }}">درباره ما</a>
                <a class="nav__link" href="{{ route('contact') }}">ارتباط با ما</a>
            </nav>

            <div class="navbar__actions">
                @auth
                    <a class="btn btn--ghost" href="{{ route('panel.library.index') }}">پنل</a>
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
