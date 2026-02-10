@extends('layouts.app')

@section('title', 'چنار آکادمی')

@section('content')

    <section class="section">
        <div class="container">
            <div class="section__head">
                <h2 class="section__title">فروش ویژه</h2>
                <div class="section__sub">پیشنهادهای ویژه برای خرید سریع و آسان</div>
            </div>

            <div class="grid grid--3">
                @foreach ($specialOffers as $offer)
                    <a class="card" href="{{ $offer->type === 'course' ? route('courses.show', $offer->slug) : route('products.show', $offer->slug) }}">
                        @php($discountLabel = $offer->discountLabel())
                        <div class="card__badge">{{ $offer->meta['badge'] ?? 'فروش ویژه' }}@if ($discountLabel) • {{ $discountLabel }} @endif</div>
                        <div class="card__title">{{ $offer->title }}</div>
                        <div class="card__price">
                            @php($currencyUnit = (($offer->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($offer->currency ?? 'IRR'))
                            @php($original = $offer->originalPrice())
                            @php($final = $offer->finalPrice())
                            @if ($offer->hasDiscount())
                                <div class="card__price--stack">
                                    <div class="card__price">
                                        <span class="price price--old">{{ number_format($original) }}</span>
                                        <span class="price__unit price__unit--old">{{ $currencyUnit }}</span>
                                    </div>
                                    <div class="card__price">
                                        <span class="price">{{ number_format($final) }}</span>
                                        <span class="price__unit">{{ $currencyUnit }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="price">{{ number_format($final) }}</span>
                                <span class="price__unit">{{ $currencyUnit }}</span>
                            @endif
                        </div>
                        <div class="card__meta">برای مشاهده و خرید کلیک کنید</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section--alt">
        <div class="container">
            <div class="section__head">
                <h2 class="section__title">آخرین جزوه‌ها و ویدیوها</h2>
                <div class="section__sub">محتوای تازه برای مطالعه و یادگیری</div>
            </div>

            <div class="grid grid--3">
                @php($placeholderThumb = 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"960\" height=\"720\" viewBox=\"0 0 960 720\"><defs><linearGradient id=\"g\" x1=\"0\" y1=\"0\" x2=\"1\" y2=\"1\"><stop offset=\"0\" stop-color=\"#0b1220\"/><stop offset=\"1\" stop-color=\"#111f37\"/></linearGradient></defs><rect width=\"960\" height=\"720\" fill=\"url(#g)\"/><rect x=\"36\" y=\"36\" width=\"888\" height=\"648\" rx=\"36\" fill=\"rgba(255,255,255,0.04)\" stroke=\"rgba(255,255,255,0.10)\"/><path d=\"M380 290c0-22 18-40 40-40h120c22 0 40 18 40 40v140c0 22-18 40-40 40H420c-22 0-40-18-40-40V290z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 310h100v100H430z\" fill=\"rgba(255,255,255,0.10)\"/><path d=\"M430 440h100\" stroke=\"rgba(255,255,255,0.20)\" stroke-width=\"16\" stroke-linecap=\"round\"/><text x=\"480\" y=\"560\" text-anchor=\"middle\" fill=\"rgba(255,255,255,0.40)\" font-family=\"Vazirmatn, sans-serif\" font-size=\"34\" font-weight=\"700\">چنار</text></svg>'))
                @foreach ($latestProducts as $item)
                    <a class="card card--media" href="{{ route('products.show', $item->slug) }}">
                        @php($thumbUrl = ($item->thumbnailMedia?->disk ?? null) === 'public' && ($item->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($item->thumbnailMedia->path) : $placeholderThumb)
                        <img class="card__cover" src="{{ $thumbUrl }}" alt="" loading="lazy"
                            onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                        @php($discountLabel = $item->discountLabel())
                        <div class="card__badge">{{ $item->type === 'video' ? 'ویدیو' : 'جزوه' }}</div>
                        @if ($discountLabel)
                            <div class="card__badge card__badge--discount">{{ $discountLabel }}</div>
                        @endif
                        @php($currencyUnit = (($item->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($item->currency ?? 'IRR'))
                        <div class="card__hover">
                            <div class="card__title">{{ $item->title }}</div>
                            <div class="card__price">
                                @php($original = $item->originalPrice())
                                @php($final = $item->finalPrice())
                                @if ($item->hasDiscount())
                                    <div class="card__price--stack">
                                        <div class="card__price">
                                            <span class="price price--old">{{ number_format($original) }}</span>
                                            <span class="price__unit price__unit--old">{{ $currencyUnit }}</span>
                                        </div>
                                        <div class="card__price">
                                            <span class="price">{{ number_format($final) }}</span>
                                            <span class="price__unit">{{ $currencyUnit }}</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="price">{{ number_format($final) }}</span>
                                    <span class="price__unit">{{ $currencyUnit }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
