@extends('layouts.spa')

@section('title', 'سبد خرید')

@section('content')
    <div class="container container--wide cart-shell">
        <div class="cart-head">
            <div>
                <h1 class="page-title">سبد خرید</h1>
            </div>
            <div class="cluster">
                <a class="btn btn--ghost" href="{{ route('courses.index') }}">دوره‌ها</a>
            </div>
        </div>

        @php($items = $items ?? collect())

        @if ($items->isEmpty())
            <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                <div class="stack stack--sm">
                    <p class="text-muted">سبد خرید شما خالی است.</p>
                    <div class="form-actions">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                        <a class="btn btn--ghost" href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
                    </div>
                </div>
            </div>
        @else
            <div class="cart-grid">
                <div class="panel cart-panel cart-panel--items">
                    <div class="cart-panel__head">
                        <div class="h4">اقلام</div>
                        <div class="text-muted text-sm"><span dir="ltr">{{ $items->count() }}</span> مورد</div>
                    </div>

                    <div class="cart-items">
                        @foreach ($items as $item)
                            @php($placeholderThumb = asset('images/default_image.webp'))
                            @php($thumbUrl = ($item->product?->thumbnailMedia?->disk ?? null) === 'public' && ($item->product?->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($item->product->thumbnailMedia->path) : $placeholderThumb)
                            <div class="cart-item">
                                <div class="cart-item__meta">
                                    <div class="cart-item__title">{{ $item->product?->title ?? 'محصول نامشخص' }}</div>
                                </div>
                                <div class="cart-item__price">
                                    <span class="price" dir="ltr">{{ number_format((int) ($item->unit_price ?? 0)) }}</span>
                                    <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                                </div>
                                <div class="cart-item__actions">
                                    <form method="post" action="{{ route('cart.items.destroy', $item->id) }}">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn--ghost btn--sm">حذف</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="panel cart-panel cart-panel--summary">
                    <div class="cart-panel__head">
                        <div class="h4">خلاصه سفارش</div>
                    </div>

                    <div class="checkout-summary">
                        <div class="checkout-kv">
                            <div class="text-muted">اقلام</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format((int) $items->count()) }}</span>
                                <span class="price__unit">عدد</span>
                            </div>
                        </div>

                        <div class="checkout-kv">
                            <div class="text-muted">جمع</div>
                            <div>
                                <span class="price" dir="ltr">{{ number_format($subtotal ?? 0) }}</span>
                                <span class="price__unit">{{ $currencyUnit ?? 'تومان' }}</span>
                            </div>
                        </div>

                        <div class="checkout-divider"></div>

                        <div class="stack stack--sm">
                            @auth
                                <a class="btn btn--primary w-full" href="{{ route('checkout.index') }}">تسویه حساب و پرداخت</a>
                            @else
                                <a class="btn btn--primary w-full" href="#" onclick="openModal('auth-modal'); return false;">ورود / ثبت نام</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
