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
            
            <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
                <div class="flex flex-col gap-6 max-w-2xl mx-auto">
                    <!-- Items -->
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <h3 class="h4 mb-4 border-b border-white/10 pb-2">اقلام سفارش</h3>
                        <div class="space-y-4">
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

                    <!-- Discount Code -->
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <form method="post" action="{{ route('checkout.coupon.apply') }}" class="flex gap-4 items-end">
                            @csrf
                            <div class="field flex-1">
                                <label class="field__label">کد تخفیف</label>
                                <input name="code" value="{{ old('code', $couponCode ?? '') }}" class="field__input" placeholder="مثلاً OFF10">
                                <div class="text-xs text-muted mt-1">برای حذف کد، فیلد را خالی کنید و اعمال را بزنید.</div>
                                @error('code')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </div>
                            <button class="btn btn--secondary mb-auto" style="margin-bottom: 22px;" type="submit">اعمال</button>
                        </form>
                    </div>

                    <!-- Invoice -->
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
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

                        <div class="flex flex-col gap-3">
                            <form method="post" action="{{ route('checkout.pay') }}" class="w-full">
                                @csrf
                                <button class="btn btn--primary w-full py-3 text-lg" type="submit">پرداخت آنلاین</button>
                            </form>
                            
                            <div class="flex gap-3">
                                <a class="btn btn--ghost flex-1 border border-white/10" href="{{ route('checkout.card-to-card.show') }}">پرداخت کارت‌به‌کارت</a>
                                <a class="btn btn--ghost flex-1 border border-white/10" href="{{ route('products.index') }}">انصراف</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
