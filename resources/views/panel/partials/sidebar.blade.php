<aside class="user-sidebar" id="panel-sidebar" data-panel-sidebar>
    <div class="stack stack--sm">
        <div class="text-center mb-4 pb-4 border-b border-gray-700">
            <div class="avatar avatar--xl mb-2 mx-auto" style="background: var(--ds-brand); color: white; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 24px;">
                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
            </div>
            <h4 class="h4">{{ auth()->user()->name ?? 'کاربر' }}</h4>
            <div class="text-sm text-muted">{{ auth()->user()->mobile ?? '' }}</div>
        </div>
        
        <nav class="stack stack--xs" data-panel-nav>
            <a href="{{ route('panel.library.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.library.*') ? 'bg-white/10' : '' }}" style="justify-content: flex-start; width: 100%;">
                کتابخانه من
            </a>
            <a href="{{ route('panel.orders.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.orders.*') ? 'bg-white/10' : '' }}" style="justify-content: flex-start; width: 100%;">
                سفارش‌ها
            </a>
            <a href="{{ route('panel.tickets.index') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.tickets.*') ? 'bg-white/10' : '' }}" style="justify-content: flex-start; width: 100%;">
                پشتیبانی
            </a>
             <a href="{{ route('panel.profile') }}" class="btn btn--ghost panel-nav-link {{ request()->routeIs('panel.profile') ? 'bg-white/10' : '' }}" style="justify-content: flex-start; width: 100%;">
                تنظیمات حساب
            </a>
             <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn--ghost text-danger" style="justify-content: flex-start; width: 100%;">خروج</button>
            </form>
        </nav>
    </div>
</aside>
