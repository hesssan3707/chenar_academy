<aside class="user-sidebar" id="panel-sidebar" data-panel-sidebar>
    <div class="stack stack--sm">
        <div class="text-center mb-4 pb-4 panel-sidebar-head">
            <div class="avatar avatar--xl mb-2 mx-auto" style="background: var(--ds-brand); color: white; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 24px;">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <h4 class="h4">{{ auth()->user()->name ?? 'کاربر' }}</h4>
            <div class="text-sm text-muted">{{ auth()->user()->mobile ?? '' }}</div>
        </div>
        
        <nav class="stack stack--xs" data-panel-nav>
            <a href="{{ route('panel.library.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.library.*') ? 'is-active' : '' }}">
                کتابخانه من
            </a>
            <a href="{{ route('panel.orders.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.orders.*') ? 'is-active' : '' }}">
                سفارش‌ها
            </a>
            <a href="{{ route('panel.tickets.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.tickets.*') ? 'is-active' : '' }}">
                پشتیبانی
            </a>
             <a href="{{ route('panel.profile') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.profile') ? 'is-active' : '' }}">
                تنظیمات حساب
            </a>
             <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn--ghost panel-nav-link text-danger">خروج</button>
            </form>
        </nav>
    </div>
</aside>
