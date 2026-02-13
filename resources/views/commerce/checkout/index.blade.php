@extends('layouts.spa')

@section('title', 'تسویه حساب')

@section('content')
    <div class="container container--wide checkout-shell">
        <div class="checkout-head">
            <div>
                <h1 class="page-title">تسویه حساب</h1>
            </div>
        </div>

        @php($items = $items ?? collect())

        @if ($items->isEmpty())
            <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                <div class="stack stack--sm">
                    <p class="text-muted">سبد خرید شما خالی است.</p>
                    <div class="form-actions">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                        <a class="btn btn--ghost" href="#" onclick="openModal('cart-modal'); return false;">بازگشت به سبد خرید</a>
                    </div>
                </div>
            </div>
        @else
            <div class="checkout-grid">
                <div class="panel checkout-panel checkout-panel--items">
                    <div class="checkout-panel__head">
                        <div class="h4">اقلام سفارش</div>
                        <a class="btn btn--ghost btn--sm" href="#" onclick="openModal('cart-modal'); return false;">ویرایش</a>
                    </div>

                    <div class="checkout-items">
                        @foreach ($items as $item)
                            <div class="checkout-item">
                                <div class="checkout-item__meta">
                                    <div class="checkout-item__title">{{ $item->product?->title ?? 'محصول' }}</div>
                                </div>
                        <div class="checkout-item__price">
                            <span class="price" dir="ltr">{{ number_format((int) $item->unit_price) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel checkout-panel">
                    <div class="checkout-panel__head">
                        <div class="h4">کد تخفیف</div>
                    </div>

                    <form method="post" action="{{ route('checkout.coupon.apply') }}" class="stack stack--sm">
                        @csrf
                        <label class="field">
                            <span class="field__label">کد تخفیف</span>
                            <input name="code" value="{{ old('code', $couponCode ?? '') }}" placeholder="مثلاً OFF10">
                            <span class="field__hint">برای حذف کد، فیلد را خالی کنید و اعمال را بزنید.</span>
                            @error('code')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                        <button class="btn btn--secondary" type="submit">اعمال</button>
                    </form>
                </div>

                <div class="panel checkout-panel checkout-panel--summary">
                    <div class="checkout-panel__head">
                        <div class="h4">فاکتور نهایی</div>
                    </div>

                    <div class="checkout-summary">
                        <div class="checkout-kv">
                            <div class="text-muted">جمع سبد خرید</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format($subtotal ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        <div class="checkout-kv checkout-kv--discount">
                            <div>تخفیف</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format($discountAmount ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        @if ((int) ($taxPercent ?? 0) > 0)
                            <div class="checkout-kv">
                            <div class="text-muted">مالیات (<span dir="ltr">{{ (int) ($taxPercent ?? 0) }}٪</span>)</div>
                                <div>
                                    <span class="price" dir="ltr">{{ number_format($taxAmount ?? 0) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                            </div>
                        @endif

                        <div class="checkout-divider"></div>

                        <div class="checkout-payable">
                            <div class="checkout-payable__label">مبلغ قابل پرداخت</div>
                            <div class="checkout-payable__value">
                                <span class="price" dir="ltr">{{ number_format($payableAmount ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        <div class="stack stack--sm">
                            <form method="post" action="{{ route('checkout.pay') }}">
                                @csrf
                            <button class="btn btn--payment w-full" style="width: 100%;" type="submit">پرداخت آنلاین</button>
                            </form>
                            <a class="btn btn--ghost w-full" href="{{ route('checkout.card-to-card.show') }}">پرداخت کارت‌به‌کارت</a>
                            <a class="btn btn--ghost w-full" href="{{ route('cart.index') }}">سبد خرید</a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
