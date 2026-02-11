<nav class="admin-nav">
    <div class="container admin-nav__inner">
        @php
            $pendingOrdersCount = \App\Models\Order::query()->whereIn('status', ['pending', 'pending_review'])->count();
            $pendingReviewsCount = \App\Models\ProductReview::query()->where('status', 'pending')->count();
            $unreadTicketsCount = \App\Models\Ticket::query()
                ->where('status', 'open')
                ->whereNotNull('last_message_at')
                ->get(['id', 'last_message_at', 'meta'])
                ->filter(function ($ticket) {
                    $meta = is_array($ticket->meta) ? $ticket->meta : [];
                    $lastReadAt = $meta['admin_last_read_at'] ?? null;
                    if (! $lastReadAt) {
                        return true;
                    }

                    try {
                        return \Illuminate\Support\Carbon::parse((string) $lastReadAt)->lt($ticket->last_message_at);
                    } catch (\Throwable) {
                        return true;
                    }
                })
                ->count();
        @endphp

        <a class="admin-nav__link" href="{{ route('admin.dashboard') }}">داشبورد</a>
        <a class="admin-nav__link" href="{{ route('admin.users.index') }}">کاربران</a>
        <a class="admin-nav__link" href="{{ route('admin.roles.index') }}">نقش‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.permissions.index') }}">دسترسی‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.categories.index') }}">دسته‌بندی‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.products.index') }}">محصولات</a>
        <a class="admin-nav__link" href="{{ route('admin.videos.index') }}">ویدیوها</a>
        <a class="admin-nav__link" href="{{ route('admin.courses.index') }}">دوره‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.orders.index') }}">
            سفارش‌ها
            @if (($pendingOrdersCount ?? 0) > 0)
                <span class="badge badge--brand" style="margin-right: 8px;">{{ (int) $pendingOrdersCount }}</span>
            @endif
        </a>
        <a class="admin-nav__link" href="{{ route('admin.reviews.index') }}">
            نظرات
            @if (($pendingReviewsCount ?? 0) > 0)
                <span class="badge badge--brand" style="margin-right: 8px;">{{ (int) $pendingReviewsCount }}</span>
            @endif
        </a>
        <a class="admin-nav__link" href="{{ route('admin.payments.index') }}">پرداخت‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.coupons.index') }}">تخفیف‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.discounts.category') }}">تخفیف گروهی</a>
        <a class="admin-nav__link" href="{{ route('admin.posts.index') }}">مقالات</a>
        <a class="admin-nav__link" href="{{ route('admin.banners.index') }}">بنرها</a>
        <a class="admin-nav__link" href="{{ route('admin.surveys.index') }}">نظرسنجی‌ها</a>
        <a class="admin-nav__link" href="{{ route('admin.settings.index') }}">تنظیمات</a>
        <a class="admin-nav__link" href="{{ route('admin.social-links.index') }}">شبکه‌های اجتماعی</a>
        <a class="admin-nav__link" href="{{ route('admin.tickets.index') }}">
            تیکت‌ها
            @if (($unreadTicketsCount ?? 0) > 0)
                <span class="badge badge--brand" style="margin-right: 8px;">{{ (int) $unreadTicketsCount }}</span>
            @endif
        </a>
        <a class="admin-nav__link" href="{{ route('admin.media.index') }}">رسانه‌ها</a>
    </div>
</nav>
