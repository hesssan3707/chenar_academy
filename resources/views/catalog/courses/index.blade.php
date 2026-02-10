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
                    @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                    @foreach ($courses as $course)
                        <a href="{{ route('courses.show', $course->slug) }}" class="card-product">
                             @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : $placeholderThumb)
                             <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" style="background-image: url('{{ $thumbUrl }}')">
                                 <div class="flex gap-2 p-2">
                                     <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">دوره</span>
                                     @if($course->hasDiscount())
                                        <span class="badge bg-red-500/80 text-white">تخفیف</span>
                                     @endif
                                 </div>
                             </div>
                             
                             <h4 class="font-bold mb-2 text-lg truncate">{{ $course->title }}</h4>
                             
                             <div class="mt-auto">
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
                                <span class="btn btn--secondary btn--sm w-full">مشاهده جزئیات</span>
                             </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
