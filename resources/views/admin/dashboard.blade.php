@extends('layouts.admin')

@section('title', 'داشبورد مدیریت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">داشبورد مدیریت</h1>
                </div>
            </div>

            <div class="grid grid--4 admin-stats" style="margin-bottom: 10px;">
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="card__meta">ثبت‌نام‌ها</div>
                        <div class="admin-stat__value">{{ number_format((int) ($usersCount ?? 0)) }}</div>
                    </div>
                </div>
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="card__meta">سفارش‌های موفق</div>
                        <div class="admin-stat__value">{{ number_format((int) ($paidOrdersCount ?? 0)) }}</div>
                    </div>
                </div>
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="card__meta">فروش کل</div>
                        <div class="admin-stat__value">{{ number_format((int) ($totalSales ?? 0)) }}</div>
                        <div class="card__meta">ریال</div>
                    </div>
                </div>
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="card__meta">محصولات</div>
                        <div class="admin-stat__value">{{ number_format((int) ($productsCount ?? 0)) }}</div>
                    </div>
                </div>
            </div>

            <div class="grid admin-grid-2">
                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="field__label">فروش ۷ روز اخیر</div>

                        @php($series = collect($salesSeries ?? []))
                        @php($maxValue = (int) max(1, (int) $series->max('total')))

                        <div class="admin-chart">
                            @foreach ($series as $point)
                                @php($height = (int) round(((int) ($point['total'] ?? 0) / $maxValue) * 100))
                                @php($pointDate = \Illuminate\Support\Carbon::parse((string) ($point['date'] ?? now()->toDateString())))
                                <div class="admin-chart__bar-wrap">
                                    <div class="admin-chart__bar" style="height: {{ max(4, $height) }}%;"></div>
                                    <div class="card__meta admin-chart__label">{{ jdate($pointDate)->format('m/d') }}</div>
                                </div>
                            @endforeach
                        </div>

                        <div class="card__meta">
                            مجموع:
                            <span class="money">
                                <span class="money__amount" dir="ltr">{{ number_format((int) $series->sum('total')) }}</span>
                                <span class="money__unit">ریال</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="field__label">وضعیت کلی</div>
                        <div class="stack stack--xs">
                            <div class="admin-kv">
                                <div class="card__meta">تیکت‌های باز</div>
                                <div class="admin-kv__value">{{ number_format((int) ($openTicketsCount ?? 0)) }}</div>
                            </div>
                            <div class="admin-kv">
                                <div class="card__meta">مقالات منتشرشده</div>
                                <div class="admin-kv__value">{{ number_format((int) ($publishedPostsCount ?? 0)) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid admin-grid-2" style="margin-top: 18px;">
                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="field__label">آنالیتیکس نشست‌ها</div>
                        @php($analytics = is_array($sessionAnalytics ?? null) ? $sessionAnalytics : [])
                        @php($deviceRows = collect((array) ($analytics['device'] ?? [])))
                        @php($countryRows = collect((array) ($analytics['countries'] ?? [])))
                        <div class="card__meta">
                            بازه:
                            {{ number_format((int) ($analytics['days'] ?? 0)) }}
                            روز
                            ·
                            نشست‌ها:
                            {{ number_format((int) ($analytics['sessions'] ?? 0)) }}
                        </div>

                        <div class="stack stack--xs">
                            <div class="admin-kv">
                                <div class="card__meta">دستگاه</div>
                                <div class="admin-kv__value" dir="ltr">
                                    @php($mobilePct = (int) ($deviceRows->firstWhere('key', 'mobile')['pct'] ?? 0))
                                    @php($webPct = (int) ($deviceRows->firstWhere('key', 'web')['pct'] ?? 0))
                                    {{ $mobilePct }}% Mobile · {{ $webPct }}% Web
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="field__label">کشورها</div>

                        @if ($countryRows->isEmpty())
                            <div class="text-muted">داده‌ای برای نمایش وجود ندارد.</div>
                        @else
                            <div class="stack stack--xs">
                                @foreach ($countryRows as $row)
                                    @php($code = (string) ($row['code'] ?? 'UNK'))
                                    @php($pct = (int) ($row['pct'] ?? 0))
                                    <div class="admin-kv">
                                        <div class="card__meta">{{ $code }}</div>
                                        <div class="admin-kv__value" dir="ltr">{{ $pct }}%</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
