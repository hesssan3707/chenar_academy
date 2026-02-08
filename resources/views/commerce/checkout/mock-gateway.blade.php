@extends('layouts.app')

@section('title', 'درگاه پرداخت آزمایشی')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">درگاه پرداخت آزمایشی</h1>
            <p class="page-subtitle">این صفحه فقط در محیط غیر پروداکشن نمایش داده می‌شود.</p>

            <div class="stack" style="margin-top: 18px;">
                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="section__title" style="font-size: 18px;">اطلاعات پرداخت</div>

                        <div class="cluster" style="justify-content: space-between;">
                            <div class="field__label">شماره سفارش</div>
                            <div>{{ $order->order_number }}</div>
                        </div>

                        <div class="cluster" style="justify-content: space-between;">
                            <div class="field__label">مبلغ قابل پرداخت</div>
                            <div>
                                <span class="price">{{ number_format((int) $payment->amount) }}</span>
                                <span class="price__unit">تومان</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--sm">
                        <div class="section__title" style="font-size: 18px;">نتیجه پرداخت</div>
                        <div class="card__meta">برای شبیه‌سازی، یکی از گزینه‌های زیر را انتخاب کنید.</div>

                        <div class="form-actions">
                            <form method="post" action="{{ route('checkout.mock-gateway.return', $payment->id) }}">
                                @csrf
                                <input type="hidden" name="result" value="success">
                                <button class="btn btn--primary" type="submit">پرداخت موفق</button>
                            </form>

                            <form method="post" action="{{ route('checkout.mock-gateway.return', $payment->id) }}">
                                @csrf
                                <input type="hidden" name="result" value="fail">
                                <button class="btn btn--ghost" type="submit">پرداخت ناموفق</button>
                            </form>
                        </div>

                        <a class="btn btn--sm btn--ghost" href="{{ route('checkout.index') }}">بازگشت به تسویه حساب</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

