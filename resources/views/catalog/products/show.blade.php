@extends('layouts.app')

@section('title', $product->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $product->title }}</h1>
            <p class="page-subtitle">{{ $product->type === 'video' ? 'ویدیو آموزشی' : 'جزوه آموزشی' }}</p>

            <div class="panel">
                <div class="stack stack--sm">
                    <div class="card__price">
                        <span class="price">{{ number_format($product->sale_price ?? $product->base_price) }}</span>
                        <span class="price__unit">تومان</span>
                    </div>

                    @if ($product->excerpt)
                        <div class="card__meta">{{ $product->excerpt }}</div>
                    @endif

                    @if ($product->description)
                        <div>{{ $product->description }}</div>
                    @endif

                    <div class="form-actions">
                        <form method="post" action="{{ route('cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button class="btn btn--primary" type="submit">افزودن به سبد</button>
                        </form>
                        <a class="btn btn--ghost" href="{{ route('products.index') }}">بازگشت</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
