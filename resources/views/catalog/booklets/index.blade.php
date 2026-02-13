@extends('layouts.spa')

@section('title', 'جزوات آموزشی')

@section('content')
    <div class="w-full h-full flex flex-col justify-center max-w-7xl mx-auto">
        @if ($activeCategory)
            <div style="position: relative; margin-bottom: 24px;">
                <a class="btn btn--ghost btn--sm" style="position: absolute; top: 0; right: 0;" href="{{ route('booklets.index') }}">
                    ← بازگشت
                </a>
                <div style="text-align: center;">
                    <span class="badge badge--brand">جزوه</span>
                    <h1 class="h2 text-white" style="margin-top: 14px;">{{ $activeCategory->title }}</h1>
                </div>
            </div>
        @else
            <div class="mb-6 text-center">
                <h1 class="h2 text-white">جزوات آموزشی</h1>
                <p class="text-muted">
                    دسترسی به منابع آموزشی دانشگاهی و تخصصی
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
                    <div class="h-scroll-container">
                        @php($placeholderThumb = asset('images/default_image.webp'))
                        @foreach ($categories as $category)
                            <a href="{{ route('booklets.index', ['category' => $category->slug]) }}" 
                                class="card-category" style="background-image: url('{{ $placeholderThumb }}'); background-size: cover; background-position: center; width: 200px;">
                                 <div class="card-category__overlay">
                                     <h3 class="card-category__title">{{ $category->title }}</h3>
                                </div>
                                <div class="info text-xs">
                                    {{ $category->products_count ?? 0 }} جزوه
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @else
                {{-- Active Category: Grouped Booklets View --}}
                @if ($groupedBooklets->isEmpty())
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700 text-center">
                        <p class="text-muted">در این دسته‌بندی جزوه‌ای یافت نشد.</p>
                    </div>
                @else
                    <div class="space-y-10 pb-10">
                        @foreach ($groupedBooklets as $institution => $booklets)
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="w-1 h-6 bg-brand rounded-full"></div>
                                    <h2 class="h3 text-white">{{ $institution }}</h2>
                                </div>
                                
                                <div class="h-scroll-container">
                                    @php($placeholderThumb = asset('images/default_image.webp'))
                                    @foreach ($booklets as $booklet)
                                        <div class="card-product">
                                            <a href="{{ route('products.show', $booklet->slug) }}" class="block">
                                                @php($thumbUrl = ($booklet->thumbnailMedia?->disk ?? null) === 'public' && ($booklet->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($booklet->thumbnailMedia->path) : $placeholderThumb)
                                                <div class="spa-cover group" style="margin-bottom: 5px;">
                                                    <img src="{{ $thumbUrl }}" alt="{{ $booklet->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                                                </div>
                                                
                                                <h3 class="card-product__title text-white line-clamp-2">{{ $booklet->title }}</h3>
                                            </a>
                                            
                                            <div class="card-product__cta">
                                                <div class="card-product__price mb-3">
                                                    <div class="card-price-row">
                                                        @php($currencyUnit = (($booklet->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($booklet->currency ?? 'IRR'))
                                                        @if($booklet->hasDiscount())
                                                            <div class="card-price-stack flex flex-col">
                                                                <span class="text-xs text-muted line-through">{{ number_format($booklet->originalPrice()) }}</span>
                                                                <span class="card-price-amount text-brand font-bold">{{ number_format($booklet->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                            </div>
                                                        @else
                                                            <div class="card-price-stack">
                                                                <span class="card-price-amount text-brand font-bold">{{ number_format($booklet->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                            </div>
                                                        @endif
                                                        <form method="post" action="{{ route('cart.items.store') }}" class="cart-inline-form">
                                                            @csrf
                                                            <input type="hidden" name="product_id" value="{{ $booklet->id }}">
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
                @endif
            @endif
        </div>
    </div>
@endsection
