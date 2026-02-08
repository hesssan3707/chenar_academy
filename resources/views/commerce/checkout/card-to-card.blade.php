@extends('layouts.app')

@section('title', 'پرداخت کارت‌به‌کارت')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">پرداخت کارت‌به‌کارت</h1>
            <p class="page-subtitle">رسید پرداخت را آپلود کنید تا سفارش برای بررسی ثبت شود.</p>

            @php($items = $items ?? collect())

            @if ($items->isEmpty())
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle">سبد خرید شما خالی است.</p>
                    <div class="form-actions">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                        <a class="btn btn--ghost" href="{{ route('checkout.index') }}">بازگشت</a>
                    </div>
                </div>
            @else
                <div class="grid" style="grid-template-columns: 1fr; gap: 18px; margin-top: 18px;">
                    <div class="panel">
                        <div class="stack stack--sm">
                            <div class="section__title" style="font-size: 18px;">فاکتور نهایی</div>

                            <div class="cluster" style="justify-content: space-between;">
                                <div class="field__label">جمع سبد خرید</div>
                                <div>
                                    <span class="price">{{ number_format($subtotal ?? 0) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>

                            <div class="cluster" style="justify-content: space-between;">
                                <div class="field__label">تخفیف</div>
                                <div>
                                    <span class="price">{{ number_format($discountAmount ?? 0) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>

                            @if ((int) ($taxPercent ?? 0) > 0)
                                <div class="cluster" style="justify-content: space-between;">
                                    <div class="field__label">مالیات ({{ (int) ($taxPercent ?? 0) }}٪)</div>
                                    <div>
                                        <span class="price">{{ number_format($taxAmount ?? 0) }}</span>
                                        <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                    </div>
                                </div>
                            @endif

                            <div class="cluster" style="justify-content: space-between;">
                                <div class="field__label">مبلغ قابل پرداخت</div>
                                <div>
                                    <span class="price">{{ number_format($payableAmount ?? 0) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel max-w-md">
                        <form method="post" action="{{ route('checkout.card-to-card.store') }}" enctype="multipart/form-data" class="stack stack--sm">
                            @csrf

                            <label class="field">
                                <span class="field__label">آپلود رسید</span>
                                <input type="file" name="receipt" required accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf">
                                <div class="field__hint">فرمت‌های مجاز: JPG، PNG یا PDF (حداکثر 5MB)</div>
                                @error('receipt')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="form-actions">
                                <button class="btn btn--primary" type="submit">ثبت برای بررسی</button>
                                <a class="btn btn--ghost" href="{{ route('checkout.index') }}">بازگشت</a>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
