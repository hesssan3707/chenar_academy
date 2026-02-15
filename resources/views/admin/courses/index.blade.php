@extends('layouts.admin')

@section('title', $title ?? 'دوره‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دوره‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت دوره‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.courses.create') }}">ایجاد دوره</a>
                </div>
            </div>

            @php($courses = $courses ?? null)
            @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
            @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')

            @if (! $courses || $courses->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز دوره‌ای ثبت نشده است.</p>
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
                            @foreach ($courses as $course)
                                <tr>
                                    <td class="admin-min-w-240">{{ $course->title }}</td>
                                    <td class="admin-nowrap">
                                        @php($statusValue = (string) ($course->status ?? ''))
                                        @if ($statusValue === 'published')
                                            <span class="badge badge--brand">منتشر شده</span>
                                        @elseif ($statusValue === 'draft')
                                            <span class="badge">پیش‌نویس</span>
                                        @else
                                            <span class="badge">{{ $statusValue !== '' ? $statusValue : '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($hasDiscount = (bool) ($course?->hasDiscount() ?? false))
                                        @php($discountType = (string) ($course->discount_type ?? ''))
                                        @php($discountValue = (int) ($course->discount_value ?? 0))
                                        @php($discountAmount = max(0, (int) $course->displayOriginalPrice($currencyCode) - (int) $course->displayFinalPrice($currencyCode)))
                                        <span class="money">
                                            <span class="money__amount" dir="ltr">{{ number_format((int) $course->displayOriginalPrice($currencyCode)) }}</span>
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
                                    <td class="admin-nowrap">{{ $course->published_at ? jdate($course->published_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <div style="display: inline-flex; gap: 8px; align-items: center;">
                                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.courses.edit', $course->id) }}">ویرایش</a>
                                            <form method="post" action="{{ route('admin.courses.destroy', $course->id) }}" class="inline-form" data-confirm="1">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
