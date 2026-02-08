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

            @if (! $coupons || $coupons->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز کد تخفیفی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
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
                                    <td>{{ $coupon->id }}</td>
                                    <td class="admin-nowrap">{{ $coupon->code }}</td>
                                    <td class="admin-nowrap">{{ $coupon->discount_type }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) $coupon->discount_value) }}</td>
                                    <td>{{ $coupon->is_active ? 'بله' : 'خیر' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->used_count ?? 0 }} / {{ $coupon->usage_limit ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">{{ $coupon->ends_at ? $coupon->ends_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.coupons.edit', $coupon->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.coupons.destroy', $coupon->id) }}" class="inline-form">
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
            @endif
        </div>
    </section>
@endsection
