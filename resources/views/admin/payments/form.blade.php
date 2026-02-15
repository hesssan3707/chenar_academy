@extends('layouts.admin')

@section('title', $title ?? 'پرداخت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'پرداخت' }}</h1>
                    <p class="page-subtitle">تنظیم وضعیت پرداخت</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.payments.index') }}">بازگشت</a>
                    @if (($payment ?? null) && $payment->exists)
                        <a class="btn btn--ghost" href="{{ route('admin.payments.show', $payment->id) }}">نمایش</a>
                    @endif
                </div>
            </div>

            @php($payment = $payment ?? null)
            @php($isEdit = $payment && $payment->exists)

            <div class="panel max-w-md">
                @if (! $isEdit)
                    <p class="page-subtitle">ثبت پرداخت از طریق فرآیند پرداخت انجام می‌شود.</p>
                @else
                    @php($gatewayLabel = match ((string) ($payment->gateway ?? '')) {
                        'card_to_card' => 'کارت‌به‌کارت',
                        'mock' => 'درگاه آزمایشی',
                        'gateway' => 'درگاه',
                        default => (string) ($payment->gateway ?? '—'),
                    })
                    @php($currencyCode = strtoupper((string) ($payment->currency ?? 'IRR')))
                    @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                    <div class="stack stack--xs">
                        <div>شناسه: {{ $payment->id }}</div>
                        <div>سفارش: {{ $payment->order_id ?? '—' }}</div>
                        <div>درگاه: {{ $gatewayLabel }}</div>
                        <div>
                            مبلغ:
                            <span class="money">
                                <span class="money__amount" dir="ltr">{{ number_format((int) ($payment->amount ?? 0)) }}</span>
                                <span class="money__unit">{{ $currencyUnit }}</span>
                            </span>
                        </div>
                        <div>Authority: {{ $payment->authority ?? '—' }}</div>
                        <div>پرداخت: {{ $payment->paid_at ? jdate($payment->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                    </div>

                    <div class="divider"></div>

                    <form method="post" action="{{ route('admin.payments.update', $payment->id) }}" class="stack stack--sm">
                        @csrf
                        @method('put')

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = old('status', (string) ($payment->status ?? 'initiated')))
                            <select name="status" required>
                                <option value="initiated" @selected($statusValue === 'initiated')>در انتظار پرداخت</option>
                                <option value="paid" @selected($statusValue === 'paid')>پرداخت شده</option>
                                <option value="failed" @selected($statusValue === 'failed')>ناموفق</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">Reference ID</span>
                            <input name="reference_id" value="{{ old('reference_id', (string) ($payment->reference_id ?? '')) }}">
                            @error('reference_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ذخیره</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
