@extends('layouts.app')

@php($pageTitle = $activeType === 'note' ? 'جزوه‌ها' : ($activeType === 'video' ? 'ویدیوها' : 'محصولات'))
@section('title', $pageTitle)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $pageTitle }}</h1>
            <p class="page-subtitle">
                @if ($activeType === 'note')
                    لیست جزوه‌های آموزشی
                @elseif ($activeType === 'video')
                    لیست ویدیوهای آموزشی
                @else
                    لیست جزوه‌ها، ویدیوها و دوره‌ها
                @endif
            </p>

            <div class="stack">
                @if (! $activeType)
                    <div class="cluster">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">همه</a>
                        <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                        <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                    </div>
                @endif

                @if ($activeType && in_array($activeType, ['note', 'video'], true) && ! ($activeCategory ?? null))
                    @php($categories = $categories ?? collect())
                    @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))

                    @if ($categories->isEmpty())
                        <div class="panel max-w-md">
                            <p class="page-subtitle" style="margin: 0;">دسته‌بندی فعالی برای نمایش وجود ندارد.</p>
                        </div>
                    @else
                        <div class="grid grid--3">
                            @foreach ($categories as $category)
                                <a class="card card--media" href="{{ route('products.index', ['type' => $activeType, 'category' => $category->slug]) }}">
                                    <img class="card__cover" src="{{ $placeholderThumb }}" alt="" loading="lazy"
                                        onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                    <div class="card__badge">دسته‌بندی</div>
                                    <div class="card__hover">
                                        <div class="card__title">{{ $category->title }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @elseif ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
                    <div class="form-actions">
                        <a class="btn btn--ghost" href="{{ route('products.index', ['type' => $activeType]) }}">بازگشت به دسته‌بندی‌ها</a>
                    </div>

                    <div class="grid grid--3">
                        @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                        @foreach ($products as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <a class="card card--media" href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}">
                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                <img class="card__cover" src="{{ $thumbUrl }}" alt="" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                @php($discountLabel = $product->discountLabel())
                                <div class="card__badge">
                                    @if ($product->type === 'course')
                                        دوره
                                    @elseif ($product->type === 'video')
                                        ویدیو
                                    @else
                                        جزوه
                                    @endif
                                    @if ($purchased) • خریداری شده @endif
                                </div>
                                    @if ($discountLabel)
                                        <div class="card__badge card__badge--discount">{{ $discountLabel }}</div>
                                    @endif
                                @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                <div class="card__hover">
                                    <div class="card__title">{{ $product->title }}</div>
                                    <div class="card__price">
                                        @php($original = $product->originalPrice())
                                        @php($final = $product->finalPrice())
                                        @if ($product->hasDiscount())
                                            <div class="card__price--stack">
                                                <div class="card__price">
                                                    <span class="price price--old">{{ number_format($original) }}</span>
                                                    <span class="price__unit price__unit--old">{{ $currencyUnit }}</span>
                                                </div>
                                                <div class="card__price">
                                                    <span class="price">{{ number_format($final) }}</span>
                                                    <span class="price__unit">{{ $currencyUnit }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="price">{{ number_format($final) }}</span>
                                            <span class="price__unit">{{ $currencyUnit }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    @if (($products ?? collect())->isEmpty())
                        <div class="panel max-w-md">
                            <p class="page-subtitle" style="margin: 0;">محتوایی برای این دسته‌بندی یافت نشد.</p>
                        </div>
                    @endif
                @else
                    <div class="grid grid--3">
                        @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                        @foreach ($products as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <a class="card card--media" href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}">
                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                <img class="card__cover" src="{{ $thumbUrl }}" alt="" loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                @php($discountLabel = $product->discountLabel())
                                <div class="card__badge">
                                    @if ($product->type === 'course')
                                        دوره
                                    @elseif ($product->type === 'video')
                                        ویدیو
                                    @else
                                        جزوه
                                    @endif
                                    @if ($purchased) • خریداری شده @endif
                                </div>
                                    @if ($discountLabel)
                                        <div class="card__badge card__badge--discount">{{ $discountLabel }}</div>
                                    @endif
                                @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                <div class="card__hover">
                                    <div class="card__title">{{ $product->title }}</div>
                                    <div class="card__price">
                                        @php($original = $product->originalPrice())
                                        @php($final = $product->finalPrice())
                                        @if ($product->hasDiscount())
                                            <div class="card__price--stack">
                                                <div class="card__price">
                                                    <span class="price price--old">{{ number_format($original) }}</span>
                                                    <span class="price__unit price__unit--old">{{ $currencyUnit }}</span>
                                                </div>
                                                <div class="card__price">
                                                    <span class="price">{{ number_format($final) }}</span>
                                                    <span class="price__unit">{{ $currencyUnit }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="price">{{ number_format($final) }}</span>
                                            <span class="price__unit">{{ $currencyUnit }}</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
