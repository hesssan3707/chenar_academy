@extends('layouts.spa')

@section('title', 'چنار آکادمی - خانه')

@section('content')
    <div class="home-page w-full max-w-7xl mx-auto h-full flex flex-col justify-center">
        @php($placeholderThumb = asset('images/default_image.webp'))
        <!-- Minimal Home Content: Two Horizontal Rows as per brief -->
        
        @if (($homeBanner ?? null))
            <div class="mb-10">
                <div class="panel p-6 bg-white/5 border border-white/10 rounded-2xl">
                    <div class="h3 text-white">{{ $homeBanner->title }}</div>
                </div>
            </div>
        @endif

        <div class="home-rows" data-home-rows>
            <!-- 1. Purchased Products (Only if logged in and has products) -->
            @auth
                @if(isset($purchasedProducts) && $purchasedProducts->isNotEmpty())
                    <div class="home-row">
                        <h2 class="home-row__title">ادامه یادگیری</h2>
                        <div class="h-scroll-container">
                            @foreach($purchasedProducts as $product)
                                <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product card-product--home">
                                    @php($thumbUrl = $product->thumbnail_url ?? $placeholderThumb)
                                    <div class="spa-cover">
                                        <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                    </div>
                                    <h3 class="card-product__title">{{ $product->title }}</h3>
                                    <div class="card-product__cta">
                                        <span class="btn btn--primary btn--sm w-full">مشاهده</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endauth

            <!-- 2. Latest Products -->
            <div class="home-row">
                <h2 class="home-row__title">جدیدترین‌ها</h2>
                <div class="h-scroll-container">
                    @foreach ($latestProducts as $item)
                        <a href="{{ $item->type === 'course' ? route('courses.show', $item->slug) : route('products.show', $item->slug) }}" class="card-product card-product--home">
                            @php($thumbUrl = ($item->thumbnailMedia?->disk ?? null) === 'public' && ($item->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($item->thumbnailMedia->path) : $placeholderThumb)
                            <div class="spa-cover">
                                <img src="{{ $thumbUrl }}" alt="{{ $item->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                    <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                        {{ $item->type === 'video' ? 'ویدیو' : ($item->type === 'course' ? 'دوره' : 'جزوه') }}
                                    </span>
                                    @if($item->hasDiscount())
                                        <span class="badge bg-red-500/80 text-white">تخفیف</span>
                                    @endif
                                </div>
                            </div>

                            <h3 class="card-product__title">{{ $item->title }}</h3>

                            <div class="card-product__cta">
                                <div class="home-price-row">
                                    @php($currencyUnit = (($item->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($item->currency ?? 'IRR'))
                                    @if($item->hasDiscount())
                                        <div class="home-price-stack">
                                            <span class="text-xs text-muted line-through">{{ number_format($item->originalPrice()) }}</span>
                                            <span class="text-brand font-bold">{{ number_format($item->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                        </div>
                                    @else
                                        <span class="text-brand font-bold">{{ number_format($item->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                    @endif
                                </div>
                                <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
