@extends('layouts.spa')

@section('title', $title ?? 'کتابخانه من')

@section('content')
    <div class="container h-full py-6">
        <div class="user-panel-grid">
            @include('panel.partials.sidebar')
            
            <main class="user-content flex flex-col overflow-hidden">
                <h2 class="h2 mb-2">{{ $title ?? 'کتابخانه من' }}</h2>
                <p class="text-muted mb-6">محتواهای خریداری‌شده فقط داخل سایت قابل مشاهده است</p>

                @php($noteItems = $noteItems ?? collect())
                @php($videoItems = $videoItems ?? collect())
                @php($hasAny = $noteItems->isNotEmpty() || $videoItems->isNotEmpty())
                @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))

                @if (! $hasAny)
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <p class="text-muted">هنوز محتوایی در کتابخانه شما ثبت نشده است.</p>
                    </div>
                @else
                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                        
                        @if ($videoItems->isNotEmpty())
                            <div class="mb-8">
                                <h3 class="h3 mb-4">ویدیوها و دوره‌ها</h3>
                                <div class="h-scroll-container">
                                    @foreach ($videoItems as $row)
                                        @php($product = $row['product'])
                                        <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product">
                                             @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                             <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" style="background-image: url('{{ $thumbUrl }}')"></div>
                                             <h4 class="font-bold mb-2 text-lg truncate">{{ $product->title }}</h4>
                                             <div class="mt-auto">
                                                 <span class="btn btn--primary btn--sm w-full">مشاهده</span>
                                             </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($noteItems->isNotEmpty())
                            <div class="mb-8">
                                <h3 class="h3 mb-4">جزوه‌ها</h3>
                                <div class="h-scroll-container">
                                     @foreach ($noteItems as $row)
                                        @php($product = $row['product'])
                                        <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product">
                                             @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                             <div class="h-48 rounded-lg bg-cover bg-center mb-4 border border-white/10" style="background-image: url('{{ $thumbUrl }}')"></div>
                                             <h4 class="font-bold mb-2 text-lg truncate">{{ $product->title }}</h4>
                                             <div class="mt-auto">
                                                 <span class="btn btn--primary btn--sm w-full">دانلود / مشاهده</span>
                                             </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection
