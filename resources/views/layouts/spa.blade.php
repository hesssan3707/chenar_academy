<!doctype html>
<html lang="fa" dir="rtl" data-theme="{{ app('theme')->active() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/') }}">
    <meta name="otp-send-url" content="{{ route('otp.send') }}">
    <meta name="cart-url" content="{{ route('cart.index') }}">
    <meta name="checkout-url" content="{{ route('checkout.index') }}">

    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/spa.css', 'resources/js/app.js'])
</head>
<body class="spa-body">
    <div class="spa-background" id="spa-bg"
        data-bg-home="{{ (string) ($spaBackgrounds['home'] ?? '') }}"
        data-bg-videos="{{ (string) ($spaBackgrounds['videos'] ?? '') }}"
        data-bg-booklets="{{ (string) ($spaBackgrounds['booklets'] ?? '') }}"
        data-bg-other="{{ (string) ($spaBackgrounds['other'] ?? '') }}">
        <div class="spa-background__layer" data-bg-layer="a"></div>
        <div class="spa-background__layer" data-bg-layer="b"></div>
    </div>

    <div class="site-loader" id="site-loader" role="status" aria-live="polite" aria-label="در حال بارگذاری" style="position:fixed;inset:0;z-index:3000;display:flex;align-items:center;justify-content:center;background:rgba(8,12,22,.92);backdrop-filter:blur(10px);">
        <div class="site-loader__inner" style="width:min(520px,92vw);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;">
            @if (empty($spaLogoUrl))
                <div class="site-loader__logo-slot" aria-hidden="true" style="width:min(280px,80vw);height:90px;border-radius:18px;border:1px dashed rgba(255,255,255,.12);background:rgba(255,255,255,.03);"></div>
            @else
                <img class="site-loader__logo" src="{{ $spaLogoUrl }}" alt="{{ config('app.name') }}" style="width:min(320px,86vw);max-height:110px;object-fit:contain;display:block;">
            @endif
            <div class="site-loader__hourglass" role="img" aria-label="در حال بارگذاری" style="color:var(--ds-brand);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6 2h12"></path>
                    <path d="M6 22h12"></path>
                    <path d="M6 2v6a6 6 0 0 0 6 6a6 6 0 0 0 6-6V2"></path>
                    <path d="M6 22v-6a6 6 0 0 1 6-6a6 6 0 0 1 6 6v6"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="spa-layout">
        <main class="spa-content-wrapper" id="spa-content">
            @php($spaPageBackgroundGroup = $spaPageBackgroundGroup ?? (request()->routeIs('home') ? 'home' : (request()->routeIs('videos.*') ? 'videos' : ((request()->routeIs('booklets.*') || request()->routeIs('notes.*')) ? 'booklets' : 'other'))))
            <div class="spa-page" data-bg-group="{{ $spaPageBackgroundGroup }}">
                @yield('content')
            </div>
        </main>

        <nav class="spa-nav">
            <a href="{{ route('home') }}" class="spa-nav-item {{ request()->routeIs('home') ? 'active' : '' }}" data-spa-nav="home">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="nav-text">خانه</span>
            </a>
            <a href="{{ route('booklets.index') }}" class="spa-nav-item {{ request()->routeIs('booklets.*') ? 'active' : '' }}" data-spa-nav="booklets">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="nav-text">جزوات</span>
            </a>
            <a href="{{ route('videos.index') }}" class="spa-nav-item {{ request()->routeIs('videos.*') ? 'active' : '' }}" data-spa-nav="videos">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span class="nav-text">ویدیوها</span>
            </a>
            <a href="{{ route('blog.index') }}" class="spa-nav-item {{ request()->routeIs('blog.*') ? 'active' : '' }}" data-spa-nav="blog">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                <span class="nav-text">وبلاگ</span>
            </a>
            @auth
                <a href="{{ route('panel.library.index') }}" class="spa-nav-item {{ request()->routeIs('panel.*') ? 'active' : '' }}" data-spa-nav="panel">
                    <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span class="nav-text">پنل</span>
                </a>
            @else
                <a href="#" class="spa-nav-item" onclick="openModal('auth-modal'); return false;" data-spa-nav="auth">
                    <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    <span class="nav-text">ورود</span>
                </a>
            @endauth
            <a href="#" class="spa-nav-item spa-nav-item--cart" data-spa-nav="cart" onclick="toggleCart(); return false;">
                <div class="relative flex items-center justify-center">
                    <svg class="spa-nav-icon mb-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span id="cart-badge" class="spa-cart-badge hidden">0</span>
                </div>
                <span class="nav-text">سبد خرید</span>
            </a>
            <a href="{{ route('about') }}" class="spa-nav-item {{ request()->routeIs('about') ? 'active' : '' }}" data-spa-nav="about">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="nav-text">درباره ما</span>
            </a>
            <a href="{{ route('contact') }}" class="spa-nav-item {{ request()->routeIs('contact') ? 'active' : '' }}" data-spa-nav="contact">
                <svg class="spa-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span class="nav-text">تماس با ما</span>
            </a>
        </nav>
    </div>

    <!-- Auth Modal -->
    <div id="auth-modal" class="spa-modal">
        <div class="spa-modal-content">
            <div class="cluster mb-4" style="justify-content: space-between;">
                 <h3 class="h3" id="auth-title">ورود</h3>
                 <button class="btn btn--ghost btn--sm" onclick="closeModal('auth-modal')">X</button>
            </div>
            
            <!-- Login View -->
            <div id="auth-view-login">
                <form action="{{ route('login.store') }}" method="POST" class="stack stack--sm" data-auth-ajax="1">
                    @csrf
                    <input type="hidden" name="action" value="login_password" id="login-action" data-login-action>

                    <div class="field__error" data-form-error hidden></div>
                    
                    <div class="field">
                        <label class="field__label">شماره موبایل</label>
                        <input type="text" name="phone" id="login-phone" class="field__input" dir="ltr" placeholder="09xxxxxxxxx" required>
                        <div class="field__error" data-field-error="phone" hidden></div>
                    </div>

                    <div id="login-password-group" data-login-section="password">
                        <div class="field">
                            <label class="field__label">رمز عبور</label>
                            <div class="input-group">
                                <input type="password" name="password" class="field__input" dir="ltr">
                                <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                    <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                        <path d="M1 1l22 22"></path>
                                        <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                        <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="field__error" data-field-error="password" hidden></div>
                        </div>
                    </div>

                    <div id="login-otp-group" data-login-section="otp" hidden>
                        <div class="field">
                            <label class="field__label">کد تایید</label>
                            <div class="input-group">
                                <input type="text" name="otp_code" class="field__input" dir="ltr" placeholder="کد ۵ رقمی">
                                <button class="btn btn--secondary btn--sm" type="button" data-otp-send data-otp-purpose="login">ارسال کد</button>
                            </div>
                            <div class="field__error" data-otp-error hidden></div>
                            <div class="field__error" data-field-error="otp_code" hidden></div>
                        </div>
                    </div>

                    <div class="stack stack--xs">
                        <div class="flex items-center justify-between">
                             <label class="cluster" style="gap: 8px; font-size: 0.9rem; color: var(--muted);">
                                <input type="checkbox" name="remember" value="1">
                                <span>مرا به خاطر بسپار</span>
                            </label>
                        </div>
                        <div class="cluster" style="gap: var(--ds-space-3);">
                            <button type="submit" class="btn btn--primary" style="flex: 1;">ورود</button>
                            <button type="button" class="btn btn--ghost" style="width: 76px;" data-login-mode-toggle>کد</button>
                        </div>
                    </div>
                    
                    <div class="cluster mt-6" style="justify-content: center; gap: var(--ds-space-4); font-size: 0.85rem; color: var(--muted);">
                        <a href="#" onclick="switchAuthView('register'); return false;" class="link">ثبت نام</a> 
                        <span style="width: 1px; height: 14px; background: var(--ds-border);"></span>
                        <a href="#" onclick="switchAuthView('forgot'); return false;" class="link">فراموشی رمز عبور</a>
                    </div>
                </form>
            </div>

            <!-- Register View -->
            <div id="auth-view-register" hidden>
                <form action="{{ route('register.store') }}" method="POST" class="stack stack--sm">
                    @csrf
                    <div class="field">
                        <label class="field__label">نام و نام خانوادگی</label>
                        <input type="text" name="name" class="field__input" required>
                    </div>
                    <div class="field">
                        <label class="field__label">شماره موبایل</label>
                        <input type="text" name="phone" id="register-phone" class="field__input" dir="ltr" placeholder="09xxxxxxxxx" required>
                    </div>
                    <div class="field">
                        <label class="field__label">رمز عبور</label>
                        <div class="input-group">
                            <input type="password" name="password" class="field__input" dir="ltr" required>
                            <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label">تکرار رمز عبور</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="field__input" dir="ltr" required>
                            <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label">کد تایید</label>
                        <div class="input-group">
                            <input type="text" name="otp_code" class="field__input" dir="ltr" required>
                            <button class="btn btn--secondary btn--sm" type="button" id="btn-send-otp-register">ارسال کد</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary w-full">ثبت نام</button>
                    <div class="cluster mt-6" style="justify-content: center; gap: var(--ds-space-4); font-size: 0.85rem; color: var(--muted);">
                        <a href="#" onclick="switchAuthView('login'); return false;" class="link">بازگشت به ورود</a>
                    </div>
                </form>
            </div>

            <!-- Forgot Password View -->
            <div id="auth-view-forgot" hidden>
                <form action="{{ route('password.forgot.store') }}" method="POST" class="stack stack--sm">
                    @csrf
                    <div class="field">
                        <label class="field__label">شماره موبایل</label>
                        <input type="text" name="phone" id="forgot-phone" class="field__input" dir="ltr" placeholder="09xxxxxxxxx" required>
                    </div>
                    <div class="field">
                        <label class="field__label">کد تایید</label>
                        <div class="input-group">
                            <input type="text" name="otp_code" class="field__input" dir="ltr" required>
                            <button class="btn btn--secondary btn--sm" type="button" id="btn-send-otp-forgot">ارسال کد</button>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label">رمز عبور جدید</label>
                        <div class="input-group">
                            <input type="password" name="password" class="field__input" dir="ltr" required>
                            <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label">تکرار رمز عبور جدید</label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="field__input" dir="ltr" required>
                            <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                    <path d="M1 1l22 22"></path>
                                    <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                    <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--primary w-full">تغییر رمز عبور</button>
                    <div class="cluster mt-6" style="justify-content: center; gap: var(--ds-space-4); font-size: 0.85rem; color: var(--muted);">
                        <a href="#" onclick="switchAuthView('login'); return false;" class="link">بازگشت به ورود</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cart Modal -->
    <div id="cart-modal" class="spa-modal">
        <div class="spa-modal-content" style="max-width: 450px;">
            <div class="cluster mb-6 pb-4 border-b border-white/10" style="justify-content: space-between;">
                 <h3 class="h3">سبد خرید</h3>
                 <button class="btn btn--ghost btn--sm" onclick="closeModal('cart-modal')">X</button>
            </div>
            
            <div id="cart-modal-items" class="stack stack--sm custom-scrollbar" style="max-height: 400px; overflow-y: auto; margin-bottom: 10px;">
                <!-- Items will be loaded here via AJAX -->
                <div class="flex flex-col items-center justify-center py-8 opacity-50">
                    <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>در حال بارگذاری...</p>
                </div>
            </div>

            <div id="cart-modal-footer" class="mt-6 pt-6 border-t border-white/10 hidden">
                <div class="flex justify-between items-center mb-10">
                    <span class="text-muted text-lg">مجموع:</span>
                    <span id="cart-total-price" class="text-2xl font-bold text-brand"></span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('cart.index') }}" class="btn btn--secondary w-full" onclick="closeModal('cart-modal')">مشاهده سبد</a>
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn--primary w-full" onclick="closeModal('cart-modal')">تسویه حساب</a>
                    @else
                        <a href="#" class="btn btn--primary w-full" onclick="closeModal('cart-modal'); openModal('auth-modal'); return false;">تسویه حساب</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script src="{{ asset('vendor/toast/toast.js') }}"></script>

    @if (($activeSurvey ?? null) && is_array($activeSurvey->options ?? null))
        <div class="modal" data-survey-modal hidden>
            <div class="modal__backdrop" data-survey-close></div>
            <div class="modal__dialog panel">
                <div class="cluster" style="justify-content: space-between; align-items: flex-start;">
                    <div class="field__label">{{ $activeSurvey->question }}</div>
                    <button class="btn btn--ghost btn--sm" type="button" data-survey-close>بستن</button>
                </div>

                <form method="post" action="{{ route('surveys.responses.store', $activeSurvey->id) }}" class="stack stack--sm"
                    style="margin-top: 12px;">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                    <div class="stack stack--xs">
                        @foreach ($activeSurvey->options as $option)
                            @php($option = is_string($option) ? trim($option) : '')
                            @if ($option !== '')
                                <label class="cluster" style="gap: 10px; align-items: center;">
                                    <input type="radio" name="answer" value="{{ $option }}" required>
                                    <span>{{ $option }}</span>
                                </label>
                            @endif
                        @endforeach
                    </div>

                    @error('answer')
                        <div class="field__error">{{ $message }}</div>
                    @enderror

                    <div class="form-actions" style="margin-top: 10px;">
                        <button class="btn btn--primary" type="submit">ثبت پاسخ</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="toast-host" data-toast-host></div>

    <div hidden aria-hidden="true">
        <span aria-label="اینستاگرام"></span>
        <span aria-label="تلگرام"></span>
        <span aria-label="یوتیوب"></span>
    </div>

    <script type="application/json" data-app-config>
        @json(['base_url' => url('/'), 'routes' => ['otp_send' => route('otp.send'), 'login' => route('login')]])
    </script>

    <script type="application/json" data-flashes>
        @json(['toast' => session('toast'), 'otp_sent' => session('otp_sent')])
    </script>
</body>
</html>
