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
                    <div>شناسه: {{ $payment->id }}</div>
                    <div>سفارش: {{ $payment->order_id ?? '—' }}</div>
                    <div>درگاه: {{ $payment->gateway ?? '—' }}</div>
                    <div>وضعیت: {{ $payment->status ?? '—' }}</div>
                    <div>مبلغ: {{ number_format((int) ($payment->amount ?? 0)) }} {{ $payment->currency ?? 'IRR' }}</div>
                    <div>Authority: {{ $payment->authority ?? '—' }}</div>
                    <div>Reference ID: {{ $payment->reference_id ?? '—' }}</div>
                    <div>پرداخت: {{ $payment->paid_at ? jdate($payment->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection
