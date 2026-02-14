@extends('layouts.spa')

@section('title', 'درگاه پرداخت آزمایشی')

@section('content')
    <div class="container h-full flex flex-col justify-center py-6">
        <h1 class="h2 mb-2 text-white">درگاه پرداخت آزمایشی</h1>
        <p class="text-muted mb-6">این صفحه فقط در محیط غیر پروداکشن نمایش داده می‌شود.</p>

        <div class="h-scroll-container">
            <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl w-80">
                <div class="stack stack--sm">
                    <div class="h4">اطلاعات پرداخت</div>
                    @php($currencyCode = strtoupper((string) ($payment->currency ?? 'IRR')))
                    @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')

                    <div class="flex justify-between">
                        <div class="text-muted">شماره سفارش</div>
                        <div>{{ $order->order_number }}</div>
                    </div>

                    <div class="flex justify-between">
                        <div class="text-muted">مبلغ قابل پرداخت</div>
                        <div>
                            <span class="price">{{ number_format((int) $payment->amount) }}</span>
                            <span class="price__unit">{{ $currencyUnit }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl w-80">
                <div class="stack stack--sm">
                    <div class="h4">نتیجه پرداخت</div>
                    <div class="text-muted">برای شبیه‌سازی، یکی از گزینه‌های زیر را انتخاب کنید.</div>

                    <div class="stack stack--sm">
                        <form method="post" action="{{ route('checkout.mock-gateway.return', $payment->id) }}">
                            @csrf
                            <input type="hidden" name="result" value="success">
                            <button class="btn btn--primary w-full" type="submit">پرداخت موفق</button>
                        </form>

                        <form method="post" action="{{ route('checkout.mock-gateway.return', $payment->id) }}">
                            @csrf
                            <input type="hidden" name="result" value="fail">
                            <button class="btn btn--ghost w-full" type="submit">پرداخت ناموفق</button>
                        </form>

                        <a class="btn btn--ghost w-full" href="{{ route('checkout.index') }}">بازگشت به تسویه حساب</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
