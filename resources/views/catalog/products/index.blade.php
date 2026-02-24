@extends('layouts.spa')

@php($pageTitle = $activeType === 'note' ? 'جزوه‌ها' : ($activeType === 'video' ? 'ویدیوها' : 'محصولات'))
@php($placeholderThumb = asset('images/default_image.webp'))
@section('title', $pageTitle)

@section('content')
    <div class="spa-page">
        @php($categories = $categories ?? collect())
        @php($products = $products ?? collect())
        @php($latestProducts = $latestProducts ?? collect())

        @if ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
            <div style="position: relative; margin-bottom: 24px;">
                <a class="btn btn--ghost btn--sm" style="position: absolute; top: 0; right: 0;" href="{{ route('products.index', ['type' => $activeType]) }}">
                    ← بازگشت
                </a>
                <div style="text-align: center;">
                    <span class="badge badge--brand">{{ $activeType === 'video' ? 'ویدیو' : 'جزوه' }}</span>
                    <h1 class="h2 text-white" style="margin-top: 14px;">{{ $activeCategory->title }}</h1>
                </div>
            </div>
        @else
            <div class="mb-6">
                <h1 class="h2 text-white">{{ $pageTitle }}</h1>
                <p class="text-muted">
                    @if ($activeType === 'note')
                        لیست جزوه‌های آموزشی
                    @elseif ($activeType === 'video')
                        بهترین انتخاب‌ها برای یادگیری ویدیو محور
                    @else
                        لیست جزوه‌ها، ویدیوها و دوره‌ها
                    @endif
                </p>
            </div>
        @endif

        @if (! $activeType)
            <div class="cluster mb-6">
                <a class="btn btn--primary" href="{{ route('products.index') }}">همه</a>
                <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
            </div>
        @endif

        @if ($activeType && in_array($activeType, ['note', 'video'], true) && ! ($activeCategory ?? null))
            @if ($categories->isEmpty())
                <div class="text-muted">دسته‌بندی فعالی برای نمایش وجود ندارد.</div>
            @else
                <div class="categories-grid">
                    @foreach ($categories->take(24) as $category)
                        @php($coverUrl = (($category->coverMedia?->disk ?? null) === 'public' && ($category->coverMedia?->path ?? null)) ? Storage::disk('public')->url($category->coverMedia->path) : $placeholderThumb)
                        <a href="{!! route('products.index', ['type' => $activeType, 'category' => $category->slug]) !!}" class="card-category" style="background-image: url('{{ $coverUrl }}'); background-size: cover; background-position: center;">
                            <div class="card-category__overlay">
                                <h3 class="card-category__title">{{ $category->title }}</h3>
                            </div>
                            <div class="info text-xs">
                                {{ $category->products_count ?? 0 }} آیتم
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($latestProducts->isNotEmpty())
                <div style="margin-top: 24px;">
                    <div class="h3 text-white" style="margin-bottom: 12px;">جدیدترین‌ها</div>
                    <div class="h-scroll-container">
                        @foreach ($latestProducts as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <div class="card-product">
                                <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="block">
                                    @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                    <div class="spa-cover" style="margin-bottom: 5px;">
                                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                        <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                            <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                                {{ $product->type === 'course' ? 'دوره' : ($product->type === 'video' ? 'ویدیو' : 'جزوه') }}
                                            </span>
                                            @if($purchased)
                                                <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h4 class="card-product__title text-white line-clamp-2">{{ $product->title }}</h4>
                                </a>

                                <div class="card-product__cta">
                                    <div class="card-product__price mb-3">
                                        <div class="card-price-row">
                                            @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
                                            @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                                            @php($originalPrice = $product->displayOriginalPrice($currencyCode))
                                            @php($finalPrice = $product->displayFinalPrice($currencyCode))
                                            @if($product->hasDiscount())
                                                <div class="card-price-stack flex flex-col">
                                                    <span class="text-xs text-muted line-through">{{ number_format($originalPrice) }}</span>
                                                    <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                </div>
                                            @else
                                                <div class="card-price-stack">
                                                    <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                </div>
                                            @endif
                                            @if (! $purchased)
                                                <form method="post" action="{{ route('cart.items.store') }}" class="cart-inline-form">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                    <button class="cart-inline-icon" type="submit" aria-label="افزودن به سبد خرید">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                            <circle cx="9" cy="21" r="1"></circle>
                                                            <circle cx="20" cy="21" r="1"></circle>
                                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
            @php($institutionGroups = $products->groupBy(fn ($p) => (int) ($p->institution_category_id ?? 0)))
            @php($institutionGroups = $institutionGroups->sortBy(fn ($group) => (string) ($group->first()?->institutionCategory?->title ?? '')))

            <div data-uni-wheel style="max-width: 100%;">
                <div class="cluster" style="justify-content: center; gap: 8px; margin-bottom: 12px;">
                    <button class="btn btn--ghost btn--sm" type="button" data-uni-wheel-prev aria-label="قبلی">
                        ↑ <span data-uni-wheel-prev-label></span>
                    </button>
                    <button class="btn btn--ghost btn--sm" type="button" data-uni-wheel-next aria-label="بعدی">
                        ↓ <span data-uni-wheel-next-label></span>
                    </button>
                </div>

                <div data-uni-wheel-viewport style="max-width: 100%;">
                    <div data-uni-wheel-track style="max-width: 100%;">
                        @foreach ($institutionGroups as $institutionId => $institutionProducts)
                            @php($institutionTitle = trim((string) ($institutionProducts->first()?->institutionCategory?->title ?? '')))
                            <div data-uni-wheel-slide data-uni-wheel-title="{{ $institutionTitle !== '' ? $institutionTitle : 'سایر' }}" style="padding-bottom: 24px; max-width: 100%;">
                                <div style="margin-bottom: 12px;">
                                    <div class="h3 text-white">{{ $institutionTitle !== '' ? $institutionTitle : 'سایر' }}</div>
                                </div>

                                <div class="h-scroll-container">
                                    @foreach ($institutionProducts as $product)
                                        @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                                        <div class="card-product">
                                            <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="block">
                                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                                <div class="spa-cover" style="margin-bottom: 5px;">
                                                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                                    <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                                        <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                                            {{ $product->type === 'course' ? 'دوره' : ($product->type === 'video' ? 'ویدیو' : 'جزوه') }}
                                                        </span>
                                                        @if($purchased)
                                                            <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <h4 class="card-product__title text-white line-clamp-2">{{ $product->title }}</h4>
                                            </a>

                                            <div class="card-product__cta">
                                                <div class="card-product__price mb-3">
                                                    <div class="card-price-row">
                                                        @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
                                                        @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                                                        @php($originalPrice = $product->displayOriginalPrice($currencyCode))
                                                        @php($finalPrice = $product->displayFinalPrice($currencyCode))
                                                        @if($product->hasDiscount())
                                                            <div class="card-price-stack flex flex-col">
                                                                <span class="text-xs text-muted line-through">{{ number_format($originalPrice) }}</span>
                                                                <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                            </div>
                                                        @else
                                                            <div class="card-price-stack">
                                                                <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                            </div>
                                                        @endif
                                                        @if (! $purchased)
                                                            <form method="post" action="{{ route('cart.items.store') }}" class="cart-inline-form">
                                                                @csrf
                                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                                <button class="cart-inline-icon" type="submit" aria-label="افزودن به سبد خرید">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <circle cx="9" cy="21" r="1"></circle>
                                                                        <circle cx="20" cy="21" r="1"></circle>
                                                                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            @if ($products->isEmpty())
                <div class="text-muted">محصولی برای نمایش وجود ندارد.</div>
            @else
                <div class="h-scroll-container">
                    @foreach ($products as $product)
                        @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                        <div class="card-product">
                            <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="block">
                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                <div class="spa-cover" style="margin-bottom: 5px;">
                                    <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                    <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                        <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                            {{ $product->type === 'course' ? 'دوره' : ($product->type === 'video' ? 'ویدیو' : 'جزوه') }}
                                        </span>
                                        @if($purchased)
                                            <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                        @endif
                                    </div>
                                </div>

                                <h4 class="card-product__title text-white line-clamp-2">{{ $product->title }}</h4>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
@endsection
