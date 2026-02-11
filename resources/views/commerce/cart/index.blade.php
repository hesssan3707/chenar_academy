@extends('layouts.spa')

@section('title', 'سبد خرید')

@section('content')
    <div class="spa-page">
        <div class="mb-10">
            <h1 class="h1 text-white mb-2">سبد خرید</h1>
            <div class="w-20 h-1 bg-brand rounded-full mb-4"></div>
            @if (($items ?? collect())->isEmpty())
                <p class="text-xl text-muted">سبد خرید شما خالی است</p>
            @else
                <p class="text-xl text-muted">محصولات انتخاب‌شده برای خرید</p>
            @endif
        </div>

        @if (($items ?? collect())->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 opacity-50">
                <svg class="w-32 h-32 mb-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="h3 text-gray-400 mb-8">سبد خرید شما خالی است.</p>
                <div class="flex gap-4">
                    <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                    <a class="btn btn--ghost" href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
                </div>
            </div>
        @else
            <div class="h-scroll-container">
                @foreach ($items as $item)
                    <div class="panel p-6 bg-white/5 border border-white/10 rounded-2xl backdrop-blur-sm w-80 flex flex-col gap-4">
                        <div class="w-full h-40 rounded-xl bg-white/10 overflow-hidden flex-shrink-0">
                            @php($thumbUrl = $item->product?->thumbnailMedia ? Storage::disk('public')->url($item->product->thumbnailMedia->path) : null)
                            @if($thumbUrl)
                                <img src="{{ $thumbUrl }}" alt="{{ $item->product?->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-500">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 text-right">
                            <h3 class="text-lg font-bold text-white mb-2 truncate">{{ $item->product?->title ?? 'محصول نامشخص' }}</h3>
                            <div class="text-brand text-lg font-bold mb-4">
                                {{ number_format($item->unit_price) }}
                                <span class="text-sm font-normal text-muted">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                            <form method="post" action="{{ route('cart.items.destroy', $item->id) }}">
                                @csrf
                                @method('delete')
                                <button type="submit" class="flex items-center gap-2 text-red-400 hover:text-red-300 transition-colors text-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span>حذف از سبد</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                <div class="panel p-8 bg-white/5 border border-white/10 rounded-3xl backdrop-blur-xl shadow-xl w-80">
                    <h3 class="h4 text-white mb-6 border-b border-white/10 pb-4">خلاصه سفارش</h3>
                    <div class="flex justify-between items-center mb-4 text-gray-300">
                        <span>تعداد اقلام:</span>
                        <span>{{ $items->count() }} عدد</span>
                    </div>
                    <div class="flex justify-between items-center mb-8 pt-4 border-t border-white/10">
                        <span class="text-lg font-bold text-white">مبلغ قابل پرداخت:</span>
                        <div class="text-brand text-2xl font-bold">
                            {{ number_format($subtotal ?? 0) }}
                            <span class="text-sm font-normal text-muted">{{ $currencyUnit ?? 'تومان' }}</span>
                        </div>
                    </div>

                    <div class="stack stack--sm">
                        @auth
                            <a class="btn btn--primary w-full py-4 text-lg shadow-lg shadow-brand/20 hover:shadow-brand/40" href="{{ route('checkout.index') }}">
                                تسویه حساب و پرداخت
                            </a>
                        @else
                            <div class="p-4 bg-brand/10 border border-brand/20 rounded-xl text-center mb-2">
                                <p class="text-sm text-brand mb-2">برای ثبت سفارش ابتدا وارد شوید</p>
                                <a href="#" onclick="openModal('auth-modal'); return false;" class="text-white font-bold hover:underline">ورود / ثبت نام</a>
                            </div>
                        @endauth
                        <a class="btn btn--ghost w-full" href="{{ route('products.index') }}">ادامه خرید</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
