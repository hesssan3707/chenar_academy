@extends('layouts.app')

@section('title', $title ?? 'کتابخانه من')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'کتابخانه من' }}</h1>
            <p class="page-subtitle">محتواهای خریداری‌شده فقط داخل سایت قابل مشاهده است</p>

            @php($noteItems = $noteItems ?? collect())
            @php($videoItems = $videoItems ?? collect())
            @php($hasAny = $noteItems->isNotEmpty() || $videoItems->isNotEmpty())

            @if (! $hasAny)
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle" style="margin: 0;">هنوز محتوایی در کتابخانه شما ثبت نشده است.</p>
                </div>
            @else
                <div class="stack" style="margin-top: 18px;">
                    @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                    <div class="stack stack--sm">
                        <div class="section__title" style="font-size: 18px;">ویدیوها</div>
                        @if ($videoItems->isEmpty())
                            <div class="card__meta">ویدیویی در کتابخانه شما وجود ندارد.</div>
                        @else
                            <div class="grid grid--3">
                                @foreach ($videoItems as $row)
                                    @php($product = $row['product'])
                                    <a class="card card--media" href="{{ route('panel.library.show', $product->slug) }}">
                                        @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                        <img class="card__cover" src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy">
                                        <div class="card__badge">{{ $product->type === 'course' ? 'دوره' : 'ویدیو' }}</div>
                                        @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                        <div class="card__hover">
                                            <div class="card__title">{{ $product->title }}</div>
                                            <div class="card__price">
                                                <span class="price">{{ number_format($product->sale_price ?? $product->base_price) }}</span>
                                                <span class="price__unit">{{ $currencyUnit }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="stack stack--sm">
                        <div class="section__title" style="font-size: 18px;">جزوه‌ها</div>
                        @if ($noteItems->isEmpty())
                            <div class="card__meta">جزوه‌ای در کتابخانه شما وجود ندارد.</div>
                        @else
                            <div class="grid grid--3">
                                @foreach ($noteItems as $row)
                                    @php($product = $row['product'])
                                    <a class="card card--media" href="{{ route('panel.library.show', $product->slug) }}">
                                        @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                        <img class="card__cover" src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy">
                                        <div class="card__badge">جزوه</div>
                                        @php($currencyUnit = (($product->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($product->currency ?? 'IRR'))
                                        <div class="card__hover">
                                            <div class="card__title">{{ $product->title }}</div>
                                            <div class="card__price">
                                                <span class="price">{{ number_format($product->sale_price ?? $product->base_price) }}</span>
                                                <span class="price__unit">{{ $currencyUnit }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
