@extends('layouts.app')

@section('title', 'دوره‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">دوره‌ها</h1>
            <p class="page-subtitle">لیست دوره‌های آموزشی</p>

            <div class="grid grid--3">
                @foreach ($courses as $course)
                    <a class="card" href="{{ route('courses.show', $course->slug) }}">
                        @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : null)
                        @if ($thumbUrl)
                            <img class="card__thumb" src="{{ $thumbUrl }}" alt="{{ $course->title }}" loading="lazy">
                        @endif
                        <div class="card__badge">دوره</div>
                        <div class="card__title">{{ $course->title }}</div>
                        @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                        <div class="card__price">
                            <span class="price">{{ number_format($course->sale_price ?? $course->base_price) }}</span>
                            <span class="price__unit">{{ $currencyUnit }}</span>
                        </div>
                        <div class="card__meta">{{ $course->excerpt ?? 'برای مشاهده جزئیات کلیک کنید' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
