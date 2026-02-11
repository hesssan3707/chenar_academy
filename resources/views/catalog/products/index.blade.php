@extends('layouts.spa')

@php($pageTitle = $activeType === 'note' ? 'جزوه‌ها' : ($activeType === 'video' ? 'ویدیوها' : 'محصولات'))
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
            @if ($activeType === 'video' && ! ($activeCategory ?? null))
                @php($videoCount = ($products ?? collect())->count())
                @php($courseCount = ($courses ?? collect())->count())
                @php($categoryCount = ($categories ?? collect())->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                    <div class="panel p-4 bg-white/5 border border-white/10 rounded-2xl">
                        <div class="text-muted text-sm">تعداد دوره‌ها</div>
                        <div class="text-white text-2xl font-bold mt-1">{{ number_format($courseCount) }}</div>
                    </div>
                    <div class="panel p-4 bg-white/5 border border-white/10 rounded-2xl">
                        <div class="text-muted text-sm">تعداد ویدیوها</div>
                        <div class="text-white text-2xl font-bold mt-1">{{ number_format($videoCount) }}</div>
                    </div>
                    <div class="panel p-4 bg-white/5 border border-white/10 rounded-2xl">
                        <div class="text-muted text-sm">دسته‌بندی‌ها</div>
                        <div class="text-white text-2xl font-bold mt-1">{{ number_format($categoryCount) }}</div>
                    </div>
                </div>
            @endif
        </div>

        @if ($activeType === 'video' && ! ($activeCategory ?? null))
            <div class="h-scroll-container">
                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 360px;">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-white font-bold">دوره‌های آموزشی</h2>
                    </div>
                    @if ($courses->isEmpty())
                        <div class="text-muted text-sm">دوره‌ای برای نمایش وجود ندارد.</div>
                    @else
                        <div class="h-scroll-container">
                            @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                            @foreach ($courses as $course)
                                <a href="{{ route('courses.show', $course->slug) }}" class="card-product" style="width: 210px; height: 300px;">
                                     @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : $placeholderThumb)
                                     <div class="h-40 rounded-lg bg-cover bg-center mb-3 border border-white/10" style="background-image: url('{{ $thumbUrl }}')">
                                         <div class="flex gap-2 p-2">
                                             <span class="badge bg-brand/80 backdrop-blur-sm text-white border border-white/10">دوره</span>
                                         </div>
                                     </div>
                                     <h4 class="font-bold mb-2 text-sm truncate">{{ $course->title }}</h4>
                                     <div class="mt-auto">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-brand font-bold text-sm">{{ number_format($course->finalPrice()) }} <span class="text-xs">تومان</span></span>
                                        </div>
                                        <span class="btn btn--secondary btn--sm w-full">مشاهده دوره</span>
                                     </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 360px;">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-white font-bold">ویدیوهای آموزشی</h2>
                    </div>
                    @if ($products->isEmpty())
                        <div class="text-muted text-sm">موردی برای نمایش وجود ندارد.</div>
                    @else
                        <div class="h-scroll-container">
                            @foreach ($products as $product)
                                @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                                <a href="{{ route('products.show', $product->slug) }}" class="card-product" style="width: 210px; height: 300px;">
                                    @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                    <div class="h-40 rounded-lg bg-cover bg-center mb-3 border border-white/10" style="background-image: url('{{ $thumbUrl }}')">
                                        <div class="flex gap-2 p-2">
                                            <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                                ویدیو
                                            </span>
                                            @if($purchased)
                                                <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                            @endif
                                        </div>
                                    </div>
                                    <h4 class="font-bold mb-2 text-sm truncate">{{ $product->title }}</h4>
                                    <div class="mt-auto">
                                        <div class="flex items-center justify-between mb-2">
                                            @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                            <span class="text-brand font-bold text-sm">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                        </div>
                                        <span class="btn btn--secondary btn--sm w-full">مشاهده</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="panel p-5 bg-white/5 border border-white/10 rounded-2xl" style="min-width: 320px;">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-white font-bold">دسته‌بندی‌ها</h2>
                    </div>
                    @php($categories = $categories ?? collect())
                    @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))

                    @if ($categories->isEmpty())
                        <div class="text-muted text-sm">دسته‌بندی فعالی برای نمایش وجود ندارد.</div>
                    @else
                        <div class="h-scroll-container">
                            @foreach ($categories as $category)
                                <a href="{{ route('products.index', ['type' => $activeType, 'category' => $category->slug]) }}" class="card-category" style="background-image: url('{{ $placeholderThumb }}'); background-size: cover; background-position: center; width: 200px;">
                                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center p-4 text-center">
                                        <h3 class="font-bold text-base text-white">{{ $category->title }}</h3>
                                    </div>
                                    <div class="info text-xs">
                                        {{ $category->products_count ?? 0 }} آیتم
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div>
                @if (! $activeType)
                    <div class="cluster mb-6">
                        <a class="btn btn--primary" href="{{ route('products.index') }}">همه</a>
                        <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                        <a class="btn btn--ghost" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                    </div>
                @endif

                @if ($activeType && in_array($activeType, ['note', 'video'], true) && ! ($activeCategory ?? null))
                    <div class="h-scroll-container">
                        @foreach ($products as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <a href="{{ route('products.show', $product->slug) }}" class="card-product">
                                @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" style="background-image: url('{{ $thumbUrl }}')">
                                    <div class="flex gap-2 p-2">
                                        <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                            {{ $product->type === 'video' ? 'ویدیو' : 'جزوه' }}
                                        </span>
                                        @if($purchased)
                                            <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                        @endif
                                    </div>
                                </div>
                                <h4 class="font-bold mb-2 text-lg truncate">{{ $product->title }}</h4>
                                <div class="mt-auto">
                                    <div class="flex items-center justify-between mb-3">
                                        @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                        <span class="text-brand font-bold">{{ number_format($product->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                    </div>
                                    <span class="btn btn--secondary btn--sm w-full">مشاهده</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif ($activeType && in_array($activeType, ['note', 'video'], true) && ($activeCategory ?? null))
                    <div class="mb-6">
                        <a class="btn btn--ghost btn--sm" href="{{ route('products.index', ['type' => $activeType]) }}">
                            ← بازگشت به دسته‌بندی‌ها
                        </a>
                        <h2 class="h3 mt-4 text-white">محصولات: {{ $activeCategory->title }}</h2>
                    </div>

                    <div class="h-scroll-container">
                        @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                        @foreach ($products as $product)
                            @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                            <a href="{{ $product->type === 'course' ? route('courses.show', $product->slug) : route('products.show', $product->slug) }}" class="card-product">
                                 @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                 <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" style="background-image: url('{{ $thumbUrl }}')">
                                     <div class="flex gap-2 p-2">
                                         <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                             {{ $product->type === 'video' ? 'ویدیو' : 'جزوه' }}
                                         </span>
                                         @if($purchased)
                                            <span class="badge bg-green-500/80 text-white">خریداری شده</span>
                                         @endif
                                     </div>
                                 </div>
                                 
                                 <h4 class="font-bold mb-2 text-lg truncate">{{ $product->title }}</h4>
                                 
                                 <div class="mt-auto">
                                    <div class="flex items-center justify-between mb-3">
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
                                    <span class="btn btn--secondary btn--sm w-full">مشاهده</span>
                                 </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="grid grid--3">
                    </div>
                @endif
            </div>
        @endif

    </div>
@endsection
