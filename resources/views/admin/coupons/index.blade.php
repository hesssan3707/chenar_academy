@extends('layouts.admin')

@section('title', $title ?? 'کدهای تخفیف')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'کدهای تخفیف' }}</h1>
                    <p class="page-subtitle">مدیریت کدهای تخفیف</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.coupons.create') }}">ایجاد کد تخفیف</a>
                </div>
            </div>

            @php($coupons = $coupons ?? null)
            @php($reportCoupon = $reportCoupon ?? null)
            @php($usageStats = $usageStats ?? null)
            @php($redemptions = $redemptions ?? null)

            @if (! $coupons || $coupons->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز کد تخفیفی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>کد</th>
                                <th>نوع</th>
                                <th>مقدار</th>
                                <th>فعال</th>
                                <th>مصرف</th>
                                <th>شروع</th>
                                <th>پایان</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($coupons as $coupon)
                                <tr>
                                    <td class="admin-nowrap">{{ $coupon->code }}</td>
                                    <td class="admin-nowrap">{{ $coupon->discount_type }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) $coupon->discount_value) }}</td>
                                    <td>{{ $coupon->is_active ? 'بله' : 'خیر' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->starts_at ? jdate($coupon->starts_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->ends_at ? jdate($coupon->ends_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm"
                                            href="{{ route('admin.coupons.index', ['report' => $coupon->id, 'page' => request()->query('page')]) }}#coupon-report">گزارش</a>
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.coupons.edit', $coupon->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.coupons.destroy', $coupon->id) }}" class="inline-form" data-confirm="1">
                                            @csrf
                                            @method('delete')
                                            <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $coupons->links() }}
                </div>

                @if ($reportCoupon && $usageStats)
                    <div id="coupon-report" class="panel" style="margin-top: 16px;">
                        <div class="stack stack--sm">
                            <div class="section__title section__title--sm">گزارش استفاده: {{ $reportCoupon->code }}</div>

                            <div class="grid admin-grid-2">
                                <div class="admin-kv">
                                    <div>تعداد استفاده</div>
                                    <div class="admin-kv__value">{{ number_format((int) ($usageStats['total_redemptions'] ?? 0)) }}</div>
                                </div>
                                <div class="admin-kv">
                                    <div>تعداد کاربران</div>
                                    <div class="admin-kv__value">{{ number_format((int) ($usageStats['unique_users'] ?? 0)) }}</div>
                                </div>
                                <div class="admin-kv">
                                    <div>مجموع تخفیف</div>
                                    <div class="admin-kv__value">{{ number_format((int) ($usageStats['total_discount_amount'] ?? 0)) }}</div>
                                </div>
                                <div class="admin-kv">
                                    <div>آخرین استفاده</div>
                                    <div class="admin-kv__value">
                                        {{ ($usageStats['last_redeemed_at'] ?? null) ? jdate($usageStats['last_redeemed_at'])->format('Y/m/d H:i') : '—' }}
                                    </div>
                                </div>
                            </div>

                            @if (! $redemptions || $redemptions->isEmpty())
                                <div class="page-subtitle" style="margin: 0;">هنوز استفاده‌ای ثبت نشده است.</div>
                            @else
                                <div class="table-wrap">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>کاربر</th>
                                                <th>سفارش</th>
                                                <th>زمان</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($redemptions as $redemption)
                                                <tr>
                                                    <td class="admin-nowrap">
                                                        @if ($redemption->user)
                                                            <a href="{{ route('admin.users.edit', $redemption->user->id) }}">
                                                                {{ $redemption->user->name ?: $redemption->user->phone ?: ('#'.$redemption->user->id) }}
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="admin-nowrap">
                                                        @if ($redemption->order)
                                                            <a href="{{ route('admin.orders.show', $redemption->order->id) }}">
                                                                {{ $redemption->order->order_number ?: ('#'.$redemption->order->id) }}
                                                            </a>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="admin-nowrap">
                                                        {{ $redemption->redeemed_at ? jdate($redemption->redeemed_at)->format('Y/m/d H:i') : '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="admin-pagination">
                                    {{ $redemptions->appends(['report' => $reportCoupon->id, 'page' => request()->query('page')])->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
