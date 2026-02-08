@extends('layouts.admin')

@section('title', $title ?? 'نمایش سفارش')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نمایش سفارش' }}</h1>
                    <p class="page-subtitle">جزئیات سفارش</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.orders.index') }}">بازگشت</a>
                    <a class="btn btn--primary" href="{{ route('admin.orders.edit', $order->id) }}">ویرایش</a>
                </div>
            </div>

            <div class="grid admin-grid-2">
                <div class="panel">
                    <h2 class="section-title">اطلاعات</h2>
                    <div class="stack stack--xs">
                        <div>شناسه: {{ $order->id }}</div>
                        <div>کاربر: {{ $order->user_id ?? '—' }}</div>
                        <div>وضعیت: {{ $order->status ?? '—' }}</div>
                        <div>مبلغ: {{ number_format((int) ($order->payable_amount ?? $order->total_amount ?? 0)) }} {{ $order->currency ?? 'IRR' }}</div>
                        <div>ایجاد: {{ $order->created_at ? jdate($order->created_at)->format('Y/m/d H:i') : '—' }}</div>
                        <div>پرداخت: {{ $order->paid_at ? jdate($order->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                        <div>لغو: {{ $order->cancelled_at ? jdate($order->cancelled_at)->format('Y/m/d H:i') : '—' }}</div>
                    </div>
                </div>

                <div class="panel">
                    <h2 class="section-title">آیتم‌ها</h2>
                    @php($items = $order->items ?? collect())
                    @if ($items->isEmpty())
                        <p class="page-subtitle">آیتمی ثبت نشده است.</p>
                    @else
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>محصول</th>
                                        <th>تعداد</th>
                                        <th>مبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $item->product_id ?? '—' }}</td>
                                            <td>{{ $item->quantity ?? 1 }}</td>
                                            <td class="admin-nowrap">{{ number_format((int) ($item->total_amount ?? $item->unit_amount ?? 0)) }} {{ $order->currency ?? 'IRR' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="panel">
                <h2 class="section-title">پرداخت‌ها</h2>
                @php($payments = $order->payments ?? collect())
                @if ($payments->isEmpty())
                    <p class="page-subtitle">پرداختی ثبت نشده است.</p>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>شناسه</th>
                                    <th>وضعیت</th>
                                    <th>مبلغ</th>
                                    <th>مرجع</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td class="admin-nowrap">{{ $payment->status ?? '—' }}</td>
                                        <td class="admin-nowrap">{{ number_format((int) ($payment->amount ?? 0)) }} {{ $payment->currency ?? ($order->currency ?? 'IRR') }}</td>
                                        <td class="admin-nowrap">{{ $payment->reference_id ?? '—' }}</td>
                                        <td class="admin-nowrap">
                                            <a class="btn btn--ghost btn--sm" href="{{ route('admin.payments.show', $payment->id) }}">نمایش</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
