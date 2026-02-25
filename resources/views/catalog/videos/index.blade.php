@extends('layouts.spa')

@section('title', 'ویدیوهای آموزشی')

@section('content')
    <div class="w-full h-full flex flex-col justify-center max-w-7xl mx-auto">
        @if ($activeCategory)
            <div style="position: relative; margin-bottom: 24px;">
                <a class="btn btn--ghost btn--sm" style="position: absolute; top: 0; right: 0;" href="{{ route('videos.index') }}">
                    ← بازگشت
                </a>
                <div style="text-align: center;">
                    <span class="badge badge--brand">ویدیو</span>
                    <h1 class="h2 text-white" style="margin-top: 14px;">{{ $activeCategory->title }}</h1>
                </div>
            </div>
        @else
            <div class="mb-6 text-center">
                <h1 class="h2 text-white">ویدیوهای آموزشی</h1>
                <p class="text-muted">
                    دسترسی به ویدیوها و دوره‌های آموزشی
                </p>
            </div>
        @endif

        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 h-full">
            @if (! $activeCategory)
                {{-- Category List View --}}
                @if ($categories->isEmpty())
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700 text-center">
                        <p class="text-muted">در حال حاضر دسته‌بندی فعالی وجود ندارد.</p>
                    </div>
                @else
                    <div class="categories-grid">
                        @php($placeholderThumb = asset('images/default_image.webp'))
                        @foreach ($categories->take(24) as $category)
                            @php($coverPath = (string) ($category->coverMedia?->path ?? ''))
                            @php($coverPath = $coverPath !== '' ? str_replace('\\', '/', $coverPath) : '')
                            @php($coverUrl = (($category->coverMedia?->disk ?? null) === 'public' && $coverPath !== '') ? Storage::disk('public')->url($coverPath) : $placeholderThumb)
                            <a href="{{ route('videos.index', ['category' => $category->slug]) }}" 
                                class="card-category" style="background-image: url('{{ $coverUrl }}'); background-size: cover; background-position: center;">
                                 <div class="card-category__overlay">
                                     <h3 class="card-category__title">{{ $category->title }}</h3>
                                </div>
                                <div class="info text-xs">
                                    {{ $category->products_count ?? 0 }} ویدیو
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

            @else
                {{-- Active Category: Grouped Videos View with University Switching --}}
                @if ($groupedVideos->isEmpty())
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700 text-center">
                        <p class="text-muted">در این دسته‌بندی ویدیویی یافت نشد.</p>
                    </div>
                @else
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
                                @foreach ($groupedVideos as $institution => $videos)
                                    <div data-uni-wheel-slide data-uni-wheel-title="{{ $institution }}" style="padding-bottom: 24px; max-width: 100%;">
                                        <div style="margin-bottom: 12px;">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="w-1 h-6 bg-brand rounded-full"></div>
                                                <h2 class="h3 text-white">{{ $institution }}</h2>
                                            </div>
                                        </div>

                                        <div class="h-scroll-container">
                                            @php($placeholderThumb = asset('images/default_image.webp'))
                                            @foreach ($videos as $video)
                                                <div class="card-product">
                                                    <a href="{{ $video->type === 'course' ? route('courses.show', $video->slug) : route('products.show', $video->slug) }}" class="block">
                                                        @php($thumbUrl = ($video->thumbnailMedia?->disk ?? null) === 'public' && ($video->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($video->thumbnailMedia->path) : $placeholderThumb)
                                                        <div class="spa-cover group" style="margin-bottom: 5px;">
                                                            <img src="{{ $thumbUrl }}" alt="{{ $video->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                                                            <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                                                <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                                                    {{ $video->type === 'course' ? 'دوره' : 'ویدیو' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <h3 class="card-product__title text-white line-clamp-2">{{ $video->title }}</h3>
                                                    </a>
                                                    
                                                    <div class="card-product__cta">
                                                        <div class="card-product__price mb-3">
                                                            <div class="card-price-row">
                                                                @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
                                                                @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
                                                                @php($originalPrice = $video->displayOriginalPrice($currencyCode))
                                                                @php($finalPrice = $video->displayFinalPrice($currencyCode))
                                                                @if($video->hasDiscount())
                                                                    <div class="card-price-stack flex flex-col">
                                                                        <span class="text-xs text-muted line-through">{{ number_format($originalPrice) }}</span>
                                                                        <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                                    </div>
                                                                @else
                                                                    <div class="card-price-stack">
                                                                        <span class="card-price-amount text-brand font-bold">{{ number_format($finalPrice) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                                    </div>
                                                                @endif
                                                                <form method="post" action="{{ route('cart.items.store') }}" class="cart-inline-form">
                                                                    @csrf
                                                                    <input type="hidden" name="product_id" value="{{ $video->id }}">
                                                                    <button class="cart-inline-icon" type="submit" aria-label="افزودن به سبد خرید">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                            <circle cx="9" cy="21" r="1"></circle>
                                                                            <circle cx="20" cy="21" r="1"></circle>
                                                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                                                        </svg>
                                                                    </button>
                                                                </form>
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
                @endif
            @endif
        </div>
    </div>
@endsection
