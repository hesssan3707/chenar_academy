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
                    <div class="stack stack--sm">
                        <div class="section__title" style="font-size: 18px;">ویدیوها</div>
                        @if ($videoItems->isEmpty())
                            <div class="card__meta">ویدیویی در کتابخانه شما وجود ندارد.</div>
                        @else
                            <div class="grid grid--3">
                                @foreach ($videoItems as $row)
                                    @php($product = $row['product'])
                                    <a class="card" href="{{ route('panel.library.show', $product->slug) }}">
                                        @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : null)
                                        @if ($thumbUrl)
                                            <img class="card__thumb" src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy">
                                        @endif
                                        <div class="card__badge">{{ $product->type === 'course' ? 'دوره' : 'ویدیو' }}</div>
                                        <div class="card__title">{{ $product->title }}</div>
                                        <div class="card__meta">{{ $product->excerpt ?? 'برای مشاهده کلیک کنید' }}</div>
                                        <div class="card__action">مشاهده</div>
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
                                    <a class="card" href="{{ route('panel.library.show', $product->slug) }}">
                                        @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : null)
                                        @if ($thumbUrl)
                                            <img class="card__thumb" src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy">
                                        @endif
                                        <div class="card__badge">جزوه</div>
                                        <div class="card__title">{{ $product->title }}</div>
                                        <div class="card__meta">{{ $product->excerpt ?? 'برای مشاهده کلیک کنید' }}</div>
                                        <div class="card__action">مشاهده</div>
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
