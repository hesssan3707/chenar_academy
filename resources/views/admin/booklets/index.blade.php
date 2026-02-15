@extends('layouts.admin')

@section('title', $title ?? 'جزوه‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'جزوه‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت جزوه‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.booklets.create') }}">ایجاد جزوه</a>
                </div>
            </div>

            @php($booklets = $booklets ?? null)
            @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
            @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')

            @if (! $booklets || $booklets->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز جزوه‌ای ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>وضعیت</th>
                                <th>قیمت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($booklets as $booklet)
                                <tr>
                                    <td class="admin-min-w-220">
                                        <div class="admin-row-title--sm">{{ $booklet->title }}</div>
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($statusValue = (string) ($booklet->status ?? ''))
                                        @if ($statusValue === 'published')
                                            <span class="badge badge--brand">منتشر شده</span>
                                        @elseif ($statusValue === 'draft')
                                            <span class="badge">پیش‌نویس</span>
                                        @else
                                            <span class="badge">{{ $statusValue !== '' ? $statusValue : '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($hasDiscount = (bool) ($booklet?->hasDiscount() ?? false))
                                        @php($discountType = (string) ($booklet->discount_type ?? ''))
                                        @php($discountValue = (int) ($booklet->discount_value ?? 0))
                                        @php($discountAmount = max(0, (int) $booklet->displayOriginalPrice($currencyCode) - (int) $booklet->displayFinalPrice($currencyCode)))
                                        <span class="money">
                                            <span class="money__amount" dir="ltr">{{ number_format((int) $booklet->displayOriginalPrice($currencyCode)) }}</span>
                                            <span class="money__unit">{{ $currencyUnit }}</span>
                                        </span>
                                        @if ($hasDiscount)
                                            @if ($discountType === 'percent' && $discountValue > 0)
                                                <span class="badge badge--danger" style="margin-inline-start: 8px;">{{ max(0, min(100, $discountValue)) }}%</span>
                                            @else
                                                <span class="badge badge--danger" style="margin-inline-start: 8px;">
                                                    <span class="money">
                                                        <span class="money__amount" dir="ltr">{{ number_format($discountAmount) }}</span>
                                                        <span class="money__unit">{{ $currencyUnit }}</span>
                                                    </span>
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">
                                        {{ $booklet->published_at ? jdate($booklet->published_at)->format('Y/m/d H:i') : '—' }}
                                    </td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.booklets.edit', $booklet->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $booklets->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
