@extends('layouts.admin')

@section('title', $title ?? 'نمایش پرداخت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نمایش پرداخت' }}</h1>
                    <p class="page-subtitle">جزئیات پرداخت</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.payments.index') }}">بازگشت</a>
                    <a class="btn btn--primary" href="{{ route('admin.payments.edit', $payment->id) }}">ویرایش</a>
                    @if ($payment->order_id)
                        <a class="btn btn--ghost" href="{{ route('admin.orders.show', $payment->order_id) }}">سفارش</a>
                    @endif
                </div>
            </div>

            <div class="panel max-w-md">
                <div class="stack stack--xs">
                    @php($gatewayLabel = match ((string) ($payment->gateway ?? '')) {
                        'card_to_card' => 'کارت‌به‌کارت',
                        'mock' => 'درگاه آزمایشی',
                        'zarinpal' => 'درگاه زرین‌پال',
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
                    @php($displayCurrency = \App\Support\Currency::current())
                    @php($currencyUnit = \App\Support\Currency::label($displayCurrency))
                    <div>شناسه: {{ $payment->id }}</div>
                    <div>سفارش: {{ $payment->order_id ?? '—' }}</div>
                    <div>درگاه: {{ $gatewayLabel }}</div>
                    <div>وضعیت: {{ $statusLabel }}</div>
                    <div>
                        مبلغ:
                        <span class="money">
                            <span class="money__amount" dir="ltr">{{ \App\Support\Currency::format((int) ($payment->amount ?? 0), $payment->currency, $displayCurrency) }}</span>
                            <span class="money__unit">{{ $currencyUnit }}</span>
                        </span>
                    </div>
                    <div>Authority: {{ $payment->authority ?? '—' }}</div>
                    <div>Reference ID: {{ $payment->reference_id ?? '—' }}</div>
                    <div>پرداخت: {{ $payment->paid_at ? jdate($payment->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection
