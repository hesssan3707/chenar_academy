@php($placeholderThumb = asset('images/default_image.webp'))
@php($purchasedProductIds = $purchasedProductIds ?? [])

@if (! $products || $products->count() === 0)
    <div class="panel max-w-md" style="margin-inline: auto;">
        <p class="page-subtitle" style="margin: 0;">محصولی برای نمایش وجود ندارد.</p>
    </div>
@else
    <div class="products-all-grid-wrap">
        <div class="products-all-grid">
            @foreach ($products as $product)
                @php($purchased = in_array($product->id, $purchasedProductIds, true))
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
    </div>

    @if (method_exists($products, 'onFirstPage') && method_exists($products, 'hasMorePages'))
        @php($showPrev = ! $products->onFirstPage())
        @php($showNext = $products->hasMorePages())
        @if ($showPrev || $showNext)
            <div class="products-all-pager">
                @if ($showPrev)
                    <a class="btn btn--ghost" href="{{ $products->previousPageUrl() }}" data-products-all-page>صفحه قبل</a>
                @endif
                @if ($showNext)
                    <a class="btn btn--primary" href="{{ $products->nextPageUrl() }}" data-products-all-page>صفحه بعد</a>
                @endif
            </div>
        @endif
    @endif
@endif
