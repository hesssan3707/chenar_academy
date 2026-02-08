@extends('layouts.app')

@section('title', 'چنار آکادمی')

@section('content')
    <section class="hero">
        <div class="container hero__inner">
            <div class="hero__content">
                <h1 class="hero__title">چنار آکادمی، فرصتی برای یادگیری و پیشرفت</h1>
                <p class="hero__subtitle">
                    آموزش ویدئویی، جزوه‌ها، وبینارهای آموزشی و محتوای تخصصی برای مسیر یادگیری شما.
                </p>

                <div class="hero__cta">
                    <a class="btn btn--primary" href="#">مشاهده ویدیوها</a>
                    <a class="btn btn--ghost" href="#">مشاهده جزوه‌ها</a>
                </div>

                <div class="hero__features">
                    @foreach ($featureItems as $item)
                        <div class="feature">
                            <div class="feature__title">{{ $item['title'] }}</div>
                            <div class="feature__desc">{{ $item['description'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="hero__visual" aria-hidden="true">
                @if ($homeBanner)
                    <div class="hero-card">
                        <div class="hero-card__badge">بنر</div>
                        <div class="hero-card__title">{{ $homeBanner->title ?: 'بنر صفحه اصلی' }}</div>
                        <div class="hero-card__meta">{{ $homeBanner->link_url ?: 'مشاهده جزئیات' }}</div>
                    </div>
                @else
                    <div class="hero-card">
                        <div class="hero-card__badge">New</div>
                        <div class="hero-card__title">ویدیوها</div>
                        <div class="hero-card__meta">مطالب جدید برای دانشگاه‌های مختلف</div>
                    </div>
                @endif
                <div class="hero-card hero-card--secondary">
                    <div class="hero-card__badge">PDF</div>
                    <div class="hero-card__title">جزوه‌ها</div>
                    <div class="hero-card__meta">خلاصه، منظم، آماده مطالعه</div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section__head">
                <h2 class="section__title">فروش ویژه</h2>
                <div class="section__sub">پیشنهادهای ویژه برای خرید سریع و آسان</div>
            </div>

            <div class="grid grid--3">
                @foreach ($specialOffers as $offer)
                    <a class="card" href="{{ $offer->type === 'course' ? route('courses.show', $offer->slug) : route('products.show', $offer->slug) }}">
                        <div class="card__badge">{{ $offer->meta['badge'] ?? 'فروش ویژه' }}</div>
                        <div class="card__title">{{ $offer->title }}</div>
                        <div class="card__price">
                            @php($currencyUnit = (($offer->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($offer->currency ?? 'IRR'))
                            <span class="price">{{ number_format($offer->sale_price ?? $offer->base_price) }}</span>
                            <span class="price__unit">{{ $currencyUnit }}</span>
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
                        <img class="card__cover" src="{{ $thumbUrl }}" alt="{{ $item->title }}" loading="lazy">
                        <div class="card__badge">{{ $item->type === 'video' ? 'ویدیو' : 'جزوه' }}</div>
                        @php($currencyUnit = (($item->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($item->currency ?? 'IRR'))
                        <div class="card__hover">
                            <div class="card__title">{{ $item->title }}</div>
                            <div class="card__price">
                                <span class="price">{{ number_format($item->sale_price ?? $item->base_price) }}</span>
                                <span class="price__unit">{{ $currencyUnit }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section__head">
                <h2 class="section__title">تازه‌ترین مقالات وبلاگ</h2>
                <div class="section__sub">مطالب مفیدی که خواندن آن را توصیه می‌کنیم</div>
            </div>

            <div class="grid grid--3">
                @foreach ($latestPosts as $post)
                    <a class="card post" href="{{ route('blog.show', $post->slug) }}">
                        <div class="post__title">{{ $post->title }}</div>
                        <div class="post__date">{{ $post->published_at ? jdate($post->published_at)->format('Y/m/d') : '' }}</div>
                        <div class="post__excerpt">{{ $post->excerpt ?? '' }}</div>
                        <div class="card__action">ادامه مطلب</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
