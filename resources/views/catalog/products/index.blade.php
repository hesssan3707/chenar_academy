@extends('layouts.spa')

@php($pageTitle = $activeType === 'note' ? 'جزوه‌ها' : ($activeType === 'video' ? 'ویدیوها' : 'محصولات'))
@php($placeholderThumb = asset('images/default_image.webp'))
@section('title', $pageTitle)

@section('content')
    <div class="spa-page">
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

        <div>
            @php($categories = $categories ?? collect())
            @php($products = $products ?? collect())

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
                    <div class="h-scroll-container">
                        @foreach ($categories as $category)
                            <a href="{!! route('products.index', ['type' => $activeType, 'category' => $category->slug]) !!}" class="card-category" style="background-image: url('{{ $placeholderThumb }}'); background-size: cover; background-position: center; width: 200px;">
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
            @elseif ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
                <div class="mb-6">
                    <a class="btn btn--ghost btn--sm" href="{{ route('products.index', ['type' => $activeType]) }}">
                        ← بازگشت به دسته‌بندی‌ها
                    </a>
                    <h2 class="h3 mt-4 text-white">محصولات: {{ $activeCategory->title }}</h2>
                </div>

                <div class="h-scroll-container">
                    @foreach ($products as $product)
                        @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                        <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="card-product">
                             @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                             <div class="spa-cover mb-4">
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

                             <div class="card-product__cta">
                                <div class="card-product__price mb-3">
                                    <div class="flex items-center justify-between">
                                        @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                        @if($product->hasDiscount())
                                            <div class="flex flex-col">
                                                <span class="text-xs text-muted line-through">{{ number_format($product->originalPrice()) }}</span>
                                                <span class="text-brand font-bold">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                            </div>
                                        @else
                                            <span class="text-brand font-bold">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                        @endif
                                    </div>
                                </div>
                                <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                             </div>
                        </a>
                    @endforeach
                </div>
            @else
                @if ($products->isEmpty())
                    <div class="text-muted">محصولی برای نمایش وجود ندارد.</div>
                @else
                    <div class="h-scroll-container">
                        @foreach ($products as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="card-product">
                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                <div class="spa-cover mb-4">
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
                                <div class="card-product__cta">
                                    <div class="card-product__price mb-3">
                                        <div class="flex items-center justify-between">
                                            @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                            @if($product->hasDiscount())
                                                <div class="flex flex-col">
                                                    <span class="text-xs text-muted line-through">{{ number_format($product->originalPrice()) }}</span>
                                                    <span class="text-brand font-bold">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                                </div>
                                            @else
                                                <span class="text-brand font-bold">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

    </div>
@endsection
