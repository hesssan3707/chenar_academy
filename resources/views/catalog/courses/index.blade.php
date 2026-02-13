@extends('layouts.spa')

@section('title', 'دوره‌ها')

@section('content')
    <div class="container h-full flex flex-col justify-center">
        <div class="mb-6">
            <h1 class="h2 text-white">دوره‌ها</h1>
            <p class="text-muted">لیست دوره‌های آموزشی</p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2">
            @if ($courses->isEmpty())
                 <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                    <p class="text-muted">دوره‌ای برای نمایش وجود ندارد.</p>
                </div>
            @else
                <div class="h-scroll-container">
                    @php($placeholderThumb = asset('images/default_image.webp'))
                    @foreach ($courses as $course)
                        <div class="card-product">
                            <a href="{{ route('courses.show', $course->slug) }}" class="block">
                             @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : $placeholderThumb)
                             <div class="spa-cover" style="margin-bottom: 5px;">
                                 <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                 <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                     <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">دوره</span>
                                     @if($course->hasDiscount())
                                        <span class="badge bg-red-500/80 text-white">تخفیف</span>
                                     @endif
                                 </div>
                             </div>
                             
                             <h4 class="card-product__title text-white line-clamp-2">{{ $course->title }}</h4>
                            </a>
                             
                             <div class="card-product__cta">
                                <div class="card-product__price mb-3">
                                    <div class="card-price-row mb-3">
                                        @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                                        @if($course->hasDiscount())
                                            <div class="card-price-stack flex flex-col">
                                                <span class="text-xs text-muted line-through">{{ number_format($course->originalPrice()) }}</span>
                                                <span class="card-price-amount text-brand font-bold">{{ number_format($course->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                            </div>
                                        @else
                                            <div class="card-price-stack">
                                                <span class="card-price-amount text-brand font-bold">{{ number_format($course->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                            </div>
                                        @endif
                                        <form method="post" action="{{ route('cart.items.store') }}" class="cart-inline-form">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $course->id }}">
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
            @endif
        </div>
    </div>
@endsection
