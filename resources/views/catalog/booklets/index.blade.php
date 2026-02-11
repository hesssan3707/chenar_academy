@extends('layouts.spa')

@section('title', 'جزوات آموزشی')

@section('content')
    <div class="w-full h-full flex flex-col justify-center max-w-7xl mx-auto">
        <div class="mb-6 text-center">
            <h1 class="h2 text-white">جزوات آموزشی</h1>
            <p class="text-muted">
                @if ($activeCategory)
                    مجموعه جزوات: {{ $activeCategory->title }}
                @else
                    دسترسی به منابع آموزشی دانشگاهی و تخصصی
                @endif
            </p>
        </div>

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
                <div class="mb-6">
                    <a class="btn btn--ghost btn--sm mb-4 inline-flex items-center gap-2" href="{{ route('booklets.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        بازگشت به دسته‌بندی‌ها
                    </a>
                </div>

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
                                        <a href="{{ route('products.show', $booklet->slug) }}" class="card-product" style="min-width: 260px;">
                                            @php($thumbUrl = ($booklet->thumbnailMedia?->disk ?? null) === 'public' && ($booklet->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($booklet->thumbnailMedia->path) : $placeholderThumb)
                                            <div class="spa-cover mb-4 group">
                                                <img src="{{ $thumbUrl }}" alt="{{ $booklet->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                                            </div>
                                            
                                            <h3 class="card-product__title text-white line-clamp-2">{{ $booklet->title }}</h3>
                                            
                                            <div class="card-product__cta">
                                                <div class="card-product__price mb-3">
                                                    <div class="flex items-center justify-between">
                                                        @php($currencyUnit = (($booklet->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($booklet->currency ?? 'IRR'))
                                                        @if($booklet->hasDiscount())
                                                            <div class="flex flex-col">
                                                                <span class="text-xs text-muted line-through">{{ number_format($booklet->originalPrice()) }}</span>
                                                                <span class="text-brand font-bold">{{ number_format($booklet->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                            </div>
                                                        @else
                                                            <span class="text-brand font-bold">{{ number_format($booklet->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                                            </div>
                                        </a>
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
