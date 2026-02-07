@extends('layouts.app')

@section('title', 'سبد خرید')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">سبد خرید</h1>

            @if (($items ?? collect())->isEmpty())
                <p class="page-subtitle">سبد خرید شما خالی است.</p>

                <div class="form-actions">
                    <a class="btn btn--primary" href="{{ route('products.index') }}">مشاهده محصولات</a>
                    <a class="btn btn--ghost" href="{{ route('courses.index') }}">مشاهده دوره‌ها</a>
                </div>
            @else
                <p class="page-subtitle">محصولات انتخاب‌شده برای خرید</p>

                <div class="stack">
                    <div class="panel">
                        <div class="stack stack--sm">
                            @foreach ($items as $item)
                                <div class="panel">
                                    <div class="stack stack--sm">
                                        <div class="cluster" style="justify-content: space-between;">
                                            <div>
                                                <div class="field__label">محصول</div>
                                                <div>{{ $item->product?->title ?? 'محصول' }}</div>
                                            </div>
                                            <div>
                                                <div class="field__label">قیمت واحد</div>
                                                <div>
                                                    <span class="price">{{ number_format($item->unit_price) }}</span>
                                                    <span class="price__unit">تومان</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cluster" style="justify-content: space-between;">
                                            <form method="post" action="{{ route('cart.items.update', $item->id) }}" class="cluster">
                                                @csrf
                                                @method('put')

                                                <label class="field" style="max-width: 180px;">
                                                    <span class="field__label">تعداد</span>
                                                    <input name="quantity" type="number" min="1" max="99" value="{{ $item->quantity }}">
                                                </label>

                                                <button class="btn btn--sm" type="submit">به‌روزرسانی</button>
                                            </form>

                                            <form method="post" action="{{ route('cart.items.destroy', $item->id) }}">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                            </form>
                                        </div>

                                        <div class="cluster" style="justify-content: space-between;">
                                            <div class="field__label">جمع این آیتم</div>
                                            <div>
                                                <span class="price">{{ number_format($item->unit_price * $item->quantity) }}</span>
                                                <span class="price__unit">تومان</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="panel">
                        <div class="cluster" style="justify-content: space-between;">
                            <div>
                                <div class="field__label">جمع سبد خرید</div>
                                <div>
                                    <span class="price">{{ number_format($subtotal ?? 0) }}</span>
                                    <span class="price__unit">تومان</span>
                                </div>
                            </div>

                            <div class="form-actions">
                                @auth
                                    <a class="btn btn--primary" href="{{ route('checkout.index') }}">ادامه خرید</a>
                                @else
                                    <div class="field__hint">برای ادامه و تسویه حساب نیاز به ورود دارید.</div>
                                    <a class="btn btn--primary" href="{{ route('login') }}">ورود</a>
                                @endauth
                                <a class="btn btn--ghost" href="{{ route('products.index') }}">ادامه خرید</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
