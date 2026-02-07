@extends('layouts.app')

@section('title', 'محصولات')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">محصولات</h1>
            <p class="page-subtitle">لیست جزوه‌ها و ویدیوهای آموزشی</p>

            <div class="stack">
                <div class="cluster">
                    <a class="btn {{ $activeType ? 'btn--ghost' : 'btn--primary' }}" href="{{ route('products.index') }}">همه</a>
                    <a class="btn {{ $activeType === 'note' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                    <a class="btn {{ $activeType === 'video' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                </div>

                <div class="grid grid--3">
                    @foreach ($products as $product)
                        <a class="card" href="{{ route('products.show', $product->slug) }}">
                            <div class="card__badge">{{ $product->type === 'video' ? 'ویدیو' : 'جزوه' }}</div>
                            <div class="card__title">{{ $product->title }}</div>
                            <div class="card__price">
                                <span class="price">{{ number_format($product->sale_price ?? $product->base_price) }}</span>
                                <span class="price__unit">تومان</span>
                            </div>
                            <div class="card__meta">{{ $product->excerpt ?? 'برای مشاهده جزئیات کلیک کنید' }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
