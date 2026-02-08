@extends('layouts.app')

@section('title', 'تسویه حساب')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">تسویه حساب</h1>

            @php($items = $items ?? collect())

            @if ($items->isEmpty())
                <p class="page-subtitle">سبد خرید شما خالی است.</p>
                <div class="form-actions">
                    <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                    <a class="btn btn--ghost" href="{{ route('cart.index') }}">بازگشت به سبد خرید</a>
                </div>
            @else
                <p class="page-subtitle">کد تخفیف و فاکتور نهایی را بررسی کنید و پرداخت را انجام دهید</p>

                <div class="stack" style="margin-top: 18px;">
                    <div class="panel">
                        <div class="stack stack--sm">
                            <div class="section__title" style="font-size: 18px;">اقلام</div>
                            @foreach ($items as $item)
                                <div class="panel">
                                    <div class="cluster" style="justify-content: space-between; align-items: center;">
                                        <div class="stack stack--xs">
                                            <div class="field__label">{{ $item->product?->title ?? 'محصول' }}</div>
                                            <div class="card__meta">
                                                <span class="price">{{ number_format((int) $item->unit_price) }}</span>
                                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                            </div>
                                        </div>
                                        <a class="btn btn--ghost btn--sm" href="{{ route('cart.index') }}">ویرایش سبد</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="panel max-w-md">
                        <form method="post" action="{{ route('checkout.coupon.apply') }}" class="stack stack--sm">
                            @csrf
                            <label class="field">
                                <span class="field__label">کد تخفیف</span>
                                <input name="code" value="{{ old('code', $couponCode ?? '') }}" placeholder="مثلاً OFF10">
                                <div class="field__hint">برای حذف کد، فیلد را خالی کنید و اعمال را بزنید.</div>
                                @error('code')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="form-actions">
                                <button class="btn btn--ghost" type="submit">اعمال</button>
                            </div>
                        </form>
                    </div>

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

                            <div class="form-actions">
                                <form method="post" action="{{ route('checkout.pay') }}">
                                    @csrf
                                    <button class="btn btn--primary" type="submit">پرداخت آنلاین</button>
                                </form>
                                <a class="btn btn--ghost" href="{{ route('checkout.card-to-card.show') }}">پرداخت کارت‌به‌کارت</a>
                                <a class="btn btn--ghost" href="{{ route('cart.index') }}">بازگشت</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
