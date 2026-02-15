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

            <div class="grid grid--3 admin-stats">
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
        </div>
    </section>
@endsection
