@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $course->title }}</h1>
            <p class="page-subtitle">دوره آموزشی</p>

            <div class="panel">
                <div class="stack stack--sm">
                    @php($thumbUrl = ($course->thumbnailMedia?->disk ?? null) === 'public' && ($course->thumbnailMedia?->path ?? null) ? Storage::disk('public')->url($course->thumbnailMedia->path) : null)
                    @if ($thumbUrl)
                        <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" style="width: 100%; max-height: 360px; object-fit: cover; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);" loading="lazy">
                    @endif

                    <div class="card__price">
                        @php($currencyUnit = (($course->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($course->currency ?? 'IRR'))
                        <span class="price">{{ number_format($course->sale_price ?? $course->base_price) }}</span>
                        <span class="price__unit">{{ $currencyUnit }}</span>
                    </div>

                    @if ($course->excerpt)
                        <div class="card__meta">{{ $course->excerpt }}</div>
                    @endif

                    @if ($course->description)
                        <div>{{ $course->description }}</div>
                    @endif

                    <div class="form-actions">
                        <form method="post" action="{{ route('cart.items.store') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $course->id }}">
                            <button class="btn btn--primary" type="submit">افزودن به سبد</button>
                        </form>
                        <a class="btn btn--ghost" href="{{ route('courses.index') }}">بازگشت</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
