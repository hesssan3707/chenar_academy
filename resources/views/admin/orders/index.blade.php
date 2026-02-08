@extends('layouts.admin')

@section('title', $title ?? 'سفارش‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'سفارش‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت سفارش‌ها</p>
                </div>
            </div>

            @php($orders = $orders ?? null)

            @if (! $orders || $orders->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز سفارشی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>کاربر</th>
                                <th>روش پرداخت</th>
                                <th>وضعیت</th>
                                <th>مبلغ</th>
                                <th>ایجاد</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                @php($gateway = ($order->payments ?? collect())->first()?->gateway)
                                @php($methodLabel = $gateway === 'card_to_card' ? 'کارت‌به‌کارت' : 'درگاه')
                                @php($statusLabel = match ((string) ($order->status ?? '')) {
                                    'pending_review' => 'در انتظار تایید',
                                    'rejected' => 'رد شده',
                                    'paid' => 'تایید شده',
                                    'cancelled' => 'لغو شده',
                                    default => (string) ($order->status ?? '—'),
                                })
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td class="admin-nowrap">{{ $order->user_id ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $methodLabel }}</td>
                                    <td class="admin-nowrap">{{ $statusLabel }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) ($order->payable_amount ?? $order->total_amount ?? 0)) }} {{ $order->currency ?? 'IRR' }}</td>
                                    <td class="admin-nowrap">{{ $order->created_at ? jdate($order->created_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.orders.show', $order->id) }}">نمایش</a>
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.orders.edit', $order->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
