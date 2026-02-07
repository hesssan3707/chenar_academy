@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $course->title }}</h1>
            <p class="page-subtitle">دوره آموزشی</p>

            <div class="panel">
                <div class="stack stack--sm">
                    <div class="card__price">
                        <span class="price">{{ number_format($course->sale_price ?? $course->base_price) }}</span>
                        <span class="price__unit">تومان</span>
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
