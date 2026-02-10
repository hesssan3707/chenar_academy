@extends('layouts.spa')

@section('title', 'چنار آکادمی - خانه')

@section('content')
    <div class="container h-full flex flex-col justify-center">
        <!-- Minimal Home Content: Two Horizontal Rows as per brief -->
        
        <!-- 1. Purchased Products (Only if logged in and has products) -->
        @auth
             <!-- Assuming we can pass $purchasedProducts from controller or view composer. 
                  If not available, we might need to rely on what's passed or fetch via ajax.
                  For now, I'll check if $purchasedProducts variable exists or if we can use a service.
                  Since I can't easily change the controller logic without checking it, I will assume the user might see this if data is passed.
                  However, the brief says "Displayed only if the user has purchased products".
                  If the controller doesn't pass it, we might need to adjust the controller.
                  Let's check HomeController.
             -->
             @if(isset($purchasedProducts) && $purchasedProducts->isNotEmpty())
                <div class="mb-10">
                    <h2 class="h3 mb-4 text-white">ادامه یادگیری</h2>
                    <div class="h-scroll-container">
                        @foreach($purchasedProducts as $product)
                            <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product">
                                <!-- Thumbnail -->
                                <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" 
                                     style="background-image: url('{{ $product->thumbnail_url }}')"></div>
                                <h3 class="font-bold text-lg truncate">{{ $product->title }}</h3>
                                <div class="mt-auto">
                                    <span class="btn btn--primary btn--sm w-full">مشاهده</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
             @endif
        @endauth

        <!-- 2. Latest Products -->
        <div>
            <h2 class="h3 mb-4 text-white">جدیدترین‌ها</h2>
            <div class="h-scroll-container">
                 @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                
                @foreach ($latestProducts as $item)
                    <a href="{{ $item->type === 'course' ? route('courses.show', $item->slug) : route('products.show', $item->slug) }}" class="card-product">
                        @php($thumbUrl = ($item->thumbnailMedia?->disk ?? null) === 'public' && ($item->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($item->thumbnailMedia->path) : $placeholderThumb)
                        <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" 
                             style="background-image: url('{{ $thumbUrl }}')">
                             <!-- Badges -->
                             <div class="flex gap-2 p-2">
                                 <span class="badge bg-black/50 backdrop-blur-sm text-white border border-white/10">
                                     {{ $item->type === 'video' ? 'ویدیو' : ($item->type === 'course' ? 'دوره' : 'جزوه') }}
                                 </span>
                                 @if($item->hasDiscount())
                                    <span class="badge bg-red-500/80 text-white">تخفیف</span>
                                 @endif
                             </div>
                        </div>
                        
                        <h3 class="font-bold text-lg mb-2 truncate">{{ $item->title }}</h3>
                        
                        <div class="mt-auto">
                            <div class="flex items-center justify-between mb-3">
                                @php($currencyUnit = (($item->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($item->currency ?? 'IRR'))
                                @if($item->hasDiscount())
                                    <div class="flex flex-col">
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
@endsection
