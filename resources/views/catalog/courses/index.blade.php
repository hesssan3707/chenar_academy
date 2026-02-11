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
                        <a href="{{ route('courses.show', $course->slug) }}" class="card-product">
                             @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : $placeholderThumb)
                             <div class="spa-cover mb-4">
                                 <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                 <div class="absolute top-0 left-0 right-0 flex gap-2 p-2">
                                     <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">دوره</span>
                                     @if($course->hasDiscount())
                                        <span class="badge bg-red-500/80 text-white">تخفیف</span>
                                     @endif
                                 </div>
                             </div>
                             
                             <h4 class="card-product__title text-white line-clamp-2">{{ $course->title }}</h4>
                             
                             <div class="card-product__cta">
                                <div class="card-product__price mb-3">
                                <div class="flex items-center justify-between mb-3">
                                    @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                                    @if($course->hasDiscount())
                                        <div class="flex flex-col">
                                            <span class="text-xs text-muted line-through">{{ number_format($course->originalPrice()) }}</span>
                                            <span class="text-brand font-bold">{{ number_format($course->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                        </div>
                                    @else
                                        <span class="text-brand font-bold">{{ number_format($course->finalPrice()) }} <span class="text-xs">{{ $currencyUnit }}</span></span>
                                    @endif
                                </div>
                                </div>
                                <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                             </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
