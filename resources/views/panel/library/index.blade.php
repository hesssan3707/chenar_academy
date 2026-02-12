@extends('layouts.spa')

@section('title', $title ?? 'کتابخانه من')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main panel-library" data-panel-main>
                <h2 class="h2 mb-2">{{ $title ?? 'کتابخانه من' }}</h2>
                <p class="text-muted mb-6">محتواهای خریداری‌شده فقط داخل سایت قابل مشاهده است</p>

                @php($noteItems = $noteItems ?? collect())
                @php($videoItems = $videoItems ?? collect())
                @php($hasAny = $noteItems->isNotEmpty() || $videoItems->isNotEmpty())
                @php($placeholderThumb = asset('images/default_image.webp'))

                @if (! $hasAny)
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <p class="text-muted">هنوز محتوایی در کتابخانه شما ثبت نشده است.</p>
                    </div>
                @else
                    @if ($videoItems->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="h3 mb-4">ویدیوها و دوره‌ها</h3>
                            <div class="h-scroll-container">
                                @foreach ($videoItems as $row)
                                    @php($product = $row['product'])
                                    <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product card-product--home">
                                         @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                         <div class="spa-cover">
                                            <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                         </div>
                                         <h3 class="card-product__title">{{ $product->title }}</h3>
                                         <div class="card-product__cta">
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
                                    <a href="{{ route('panel.library.show', $product->slug) }}" class="card-product card-product--home">
                                         @php($thumbUrl = ($product->thumbnailMedia?->disk ?? null) === 'public' && ($product->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($product->thumbnailMedia->path) : $placeholderThumb)
                                         <div class="spa-cover">
                                            <img src="{{ $thumbUrl }}" alt="{{ $product->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                                         </div>
                                         <h3 class="card-product__title">{{ $product->title }}</h3>
                                         <div class="card-product__cta">
                                             <span class="btn btn--primary btn--sm w-full">دانلود / مشاهده</span>
                                         </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>
@endsection
