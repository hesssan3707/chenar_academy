@extends('layouts.admin')

@section('title', $title ?? 'پرداخت‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'پرداخت‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت پرداخت‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.payments.create') }}">ایجاد پرداخت</a>
                </div>
            </div>

            @php($payments = $payments ?? null)

            @if (! $payments || $payments->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز پرداختی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>سفارش</th>
                                <th>درگاه</th>
                                <th>وضعیت</th>
                                <th>مبلغ</th>
                                <th>مرجع</th>
                                <th>پرداخت</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                @php($gatewayLabel = match ((string) ($payment->gateway ?? '')) {
                                    'card_to_card' => 'کارت‌به‌کارت',
                                    'mock' => 'درگاه آزمایشی',
                                    'gateway' => 'درگاه',
                                    default => (string) ($payment->gateway ?? '—'),
                                })
                                @php($statusLabel = match ((string) ($payment->status ?? '')) {
                                    'initiated' => 'در انتظار پرداخت',
                                    'pending_review' => 'در انتظار تایید',
                                    'paid' => 'پرداخت شده',
                                    'failed' => 'ناموفق',
                                    'rejected' => 'رد شده',
                                    default => (string) ($payment->status ?? '—'),
                                })
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td class="admin-nowrap">{{ $payment->order_id ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $gatewayLabel }}</td>
                                    <td class="admin-nowrap">{{ $statusLabel }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) ($payment->amount ?? 0)) }} {{ $payment->currency ?? 'IRR' }}</td>
                                    <td class="admin-nowrap">{{ $payment->reference_id ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $payment->paid_at ? jdate($payment->paid_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.payments.show', $payment->id) }}">نمایش</a>
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.payments.edit', $payment->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
