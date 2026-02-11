@extends('layouts.spa')

@section('title', 'تسویه حساب')

@section('content')
    <div class="container h-full flex flex-col justify-center py-6">
        <h1 class="h2 mb-2 text-white">تسویه حساب</h1>
        
        @php($items = $items ?? collect())

        @if ($items->isEmpty())
             <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                <p class="text-muted mb-4">سبد خرید شما خالی است.</p>
                 <div class="flex gap-4">
                    <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                    <a class="btn btn--ghost text-white/70 hover:text-white" href="#" onclick="openModal('cart-modal'); return false;">بازگشت به سبد خرید</a>
                </div>
            </div>
        @else
            <p class="text-muted mb-6">کد تخفیف و فاکتور نهایی را بررسی کنید و پرداخت را انجام دهید</p>
            <div class="h-scroll-container">
                <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl w-80">
                    <h3 class="h4 mb-4 border-b border-white/10 pb-2">اقلام سفارش</h3>
                    <div class="stack stack--sm">
                        @foreach ($items as $item)
                            <div class="flex items-center justify-between p-3 bg-black/20 rounded-lg">
                                <div>
                                    <div class="font-bold">{{ $item->product?->title ?? 'محصول' }}</div>
                                    <div class="text-sm text-brand mt-1">
                                        {{ number_format((int) $item->unit_price) }} <span class="text-xs">{{ $currencyUnit ?? 'تومان' }}</span>
                                    </div>
                                </div>
                                <a class="btn btn--ghost btn--sm text-white/50 hover:text-white" href="#" onclick="openModal('cart-modal'); return false;">ویرایش</a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl w-80">
                    <h3 class="h4 mb-4 border-b border-white/10 pb-2">کد تخفیف</h3>
                    <form method="post" action="{{ route('checkout.coupon.apply') }}" class="stack stack--sm">
                        @csrf
                        <div class="field">
                            <label class="field__label">کد تخفیف</label>
                            <input name="code" value="{{ old('code', $couponCode ?? '') }}" class="field__input" placeholder="مثلاً OFF10">
                            <div class="text-xs text-muted mt-1">برای حذف کد، فیلد را خالی کنید و اعمال را بزنید.</div>
                            @error('code')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button class="btn btn--secondary" type="submit">اعمال</button>
                    </form>
                </div>

                <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl w-80">
                    <h3 class="h4 mb-4 border-b border-white/10 pb-2">فاکتور نهایی</h3>
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between">
                            <span class="text-muted">جمع سبد خرید</span>
                            <span>{{ number_format($subtotal ?? 0) }} <span class="text-xs">{{ $currencyUnit ?? 'تومان' }}</span></span>
                        </div>
                        <div class="flex justify-between text-green-400">
                            <span>تخفیف</span>
                            <span>{{ number_format($discountAmount ?? 0) }} <span class="text-xs">{{ $currencyUnit ?? 'تومان' }}</span></span>
                        </div>
                        @if ((int) ($taxPercent ?? 0) > 0)
                            <div class="flex justify-between text-muted">
                                <span>مالیات ({{ (int) ($taxPercent ?? 0) }}٪)</span>
                                <span>{{ number_format($taxAmount ?? 0) }} <span class="text-xs">{{ $currencyUnit ?? 'تومان' }}</span></span>
                            </div>
                        @endif
                        <div class="h-px bg-white/10 my-2"></div>
                        <div class="flex justify-between text-xl font-bold text-brand">
                            <span>مبلغ قابل پرداخت</span>
                            <span>{{ number_format($payableAmount ?? 0) }} <span class="text-xs">{{ $currencyUnit ?? 'تومان' }}</span></span>
                        </div>
                    </div>
                    <div class="stack stack--sm">
                        <form method="post" action="{{ route('checkout.pay') }}">
                            @csrf
                            <button class="btn btn--primary w-full py-3 text-lg" type="submit">پرداخت آنلاین</button>
                        </form>
                        <a class="btn btn--ghost w-full" href="{{ route('checkout.card-to-card.show') }}">پرداخت کارت‌به‌کارت</a>
                        <a class="btn btn--ghost w-full" href="{{ route('products.index') }}">انصراف</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
