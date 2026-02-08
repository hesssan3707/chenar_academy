<!doctype html>
<html lang="fa" dir="rtl" data-theme="admin">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        <title>@yield('title', config('app.name'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="admin-shell">
            <aside class="admin-sidebar">
                <a class="admin-brand" href="{{ route('admin.dashboard') }}">
                    <span class="admin-brand__mark">چنار</span>
                    <span class="admin-brand__name">مدیریت</span>
                </a>

                <nav class="admin-menu">
                    <a class="admin-menu__link @if (request()->routeIs('admin.dashboard')) is-active @endif" href="{{ route('admin.dashboard') }}">داشبورد</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.users.*')) is-active @endif" href="{{ route('admin.users.index') }}">کاربران</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.booklets.*')) is-active @endif" href="{{ route('admin.booklets.index') }}">جزوه‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.videos.*')) is-active @endif" href="{{ route('admin.videos.index') }}">ویدیوها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.tickets.*')) is-active @endif" href="{{ route('admin.tickets.index') }}">تیکت‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.surveys.*')) is-active @endif" href="{{ route('admin.surveys.index') }}">نظرسنجی‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.posts.*')) is-active @endif" href="{{ route('admin.posts.index') }}">مقالات</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.settings.*')) is-active @endif" href="{{ route('admin.settings.index') }}">تنظیمات</a>

                    <div class="admin-menu__divider"></div>

                    <a class="admin-menu__link @if (request()->routeIs('admin.categories.*')) is-active @endif" href="{{ route('admin.categories.index') }}">دسته‌بندی‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.products.*')) is-active @endif" href="{{ route('admin.products.index') }}">محصولات</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.courses.*')) is-active @endif" href="{{ route('admin.courses.index') }}">دوره‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.orders.*')) is-active @endif" href="{{ route('admin.orders.index') }}">سفارش‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.payments.*')) is-active @endif" href="{{ route('admin.payments.index') }}">پرداخت‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.media.*')) is-active @endif" href="{{ route('admin.media.index') }}">رسانه‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.roles.*')) is-active @endif" href="{{ route('admin.roles.index') }}">نقش‌ها</a>
                    <a class="admin-menu__link @if (request()->routeIs('admin.permissions.*')) is-active @endif" href="{{ route('admin.permissions.index') }}">دسترسی‌ها</a>
                </nav>
            </aside>

            <div class="admin-main">
                <header class="admin-topbar">
                    <div class="admin-topbar__title">@yield('title', 'مدیریت')</div>
                    <div class="admin-topbar__actions">
                        <form class="admin-topbar__search" method="get" action="{{ route('admin.users.index') }}">
                            <input type="search" name="q" placeholder="جستجوی کاربر" value="{{ request('q') }}">
                        </form>
                        @if (($adminScopedUser ?? null))
                            <span class="badge badge--brand">فیلتر: {{ $adminScopedUser->name ?: $adminScopedUser->phone }}</span>
                            <form method="post" action="{{ route('admin.scope.clear') }}">
                                @csrf
                                <button class="btn btn--ghost btn--sm" type="submit">حذف فیلتر</button>
                            </form>
                        @endif
                        <a class="btn btn--ghost btn--sm" href="{{ route('home') }}">سایت</a>
                        <span class="admin-topbar__user">{{ auth()->user()?->name ?: auth()->user()?->phone }}</span>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn--ghost btn--sm" type="submit">خروج</button>
                        </form>
                    </div>
                </header>

                <main class="admin-content">
                    @yield('content')
                </main>
            </div>
        </div>

        <div class="toast-host" data-toast-host></div>

        <script type="application/json" data-app-config>
            @json(['base_url' => url('/'), 'routes' => ['otp_send' => route('otp.send')]])
        </script>

        <script type="application/json" data-flashes>
            @json(['toast' => session('toast'), 'otp_sent' => session('otp_sent')])
        </script>
    </body>
</html>
