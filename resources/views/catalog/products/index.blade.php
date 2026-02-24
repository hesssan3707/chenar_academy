@extends('layouts.spa')

@php($pageTitle = $activeType === 'note' ? 'جزوه‌ها' : ($activeType === 'video' ? 'ویدیوها' : 'محصولات'))
@php($placeholderThumb = asset('images/default_image.webp'))
@section('title', $pageTitle)

@section('content')
    <div class="spa-page">
        @php($categories = $categories ?? collect())
        @php($products = $products ?? collect())
        @php($q = $q ?? '')
        @php($activeCategorySlug = trim((string) request()->query('category', '')))

        @if ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
            <div style="position: relative; margin-bottom: 24px;">
                <a class="btn btn--ghost btn--sm" style="position: absolute; top: 0; right: 0;"
                    href="{{ route('products.index', array_filter(['type' => $activeType, 'q' => $q])) }}">
                    ← بازگشت
                </a>
                <div style="text-align: center;">
                    <span class="badge badge--brand">{{ $activeType === 'video' ? 'ویدیو' : 'جزوه' }}</span>
                    <h1 class="h2 text-white" style="margin-top: 14px;">{{ $activeCategory->title }}</h1>
                </div>
            </div>
        @else
            <div class="mb-4">
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

        <form method="get" action="{{ route('products.index') }}" class="cluster mb-4"
            style="justify-content: center; gap: 10px;">
            @if ($activeType)
                <input type="hidden" name="type" value="{{ $activeType }}">
            @endif
            @if ($activeCategorySlug !== '')
                <input type="hidden" name="category" value="{{ $activeCategorySlug }}">
            @endif
            <input type="search" name="q" dir="rtl" placeholder="جستجوی محصول..." value="{{ $q }}"
                style="max-width: 520px;">
            <button class="btn btn--primary" type="submit">جستجو</button>
            @if ($q !== '')
                <a class="btn btn--ghost" href="{{ route('products.index', array_filter(['type' => $activeType, 'category' => $activeCategorySlug])) }}">پاک کردن</a>
            @endif
        </form>

        <div class="cluster mb-4" style="justify-content: center;">
            <a class="btn {{ ! $activeType ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index') }}">همه</a>
            <a class="btn {{ $activeType === 'note' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', array_filter(['type' => 'note', 'q' => $q])) }}">جزوه‌ها</a>
            <a class="btn {{ $activeType === 'video' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', array_filter(['type' => 'video', 'q' => $q])) }}">ویدیوها</a>
        </div>

        @if ($activeType && in_array($activeType, ['note', 'video'], true))
            @if ($categories->isNotEmpty())
                <div class="cluster mb-6" style="justify-content: center;">
                    <a class="btn btn--sm {{ $activeCategorySlug === '' ? 'btn--primary' : 'btn--ghost' }}"
                        href="{{ route('products.index', array_filter(['type' => $activeType, 'q' => $q])) }}">
                        همه دسته‌بندی‌ها
                    </a>
                    @foreach ($categories as $category)
                        <a class="btn btn--sm {{ $activeCategorySlug === (string) $category->slug ? 'btn--primary' : 'btn--ghost' }}"
                            href="{{ route('products.index', array_filter(['type' => $activeType, 'category' => $category->slug, 'q' => $q])) }}">
                            {{ $category->title }}
                            <span class="text-muted text-sm" dir="ltr">({{ (int) ($category->products_count ?? 0) }})</span>
                        </a>
                    @endforeach
                </div>
            @endif
        @endif

        @if ($products->count() === 0)
            <div class="panel max-w-md" style="margin-inline: auto;">
                <p class="page-subtitle" style="margin: 0;">محصولی برای نمایش وجود ندارد.</p>
            </div>
        @else
            <div class="products-grid">
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
                                    @if ($purchased)
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
                                    @if ($product->hasDiscount())
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

            @if (method_exists($products, 'links'))
                <div style="display: flex; justify-content: center; margin-top: 18px;">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
