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
                        <div class="card__badge">دوره</div>
                        <div class="card__title">{{ $course->title }}</div>
                        <div class="card__price">
                            <span class="price">{{ number_format($course->sale_price ?? $course->base_price) }}</span>
                            <span class="price__unit">تومان</span>
                        </div>
                        <div class="card__meta">{{ $course->excerpt ?? 'برای مشاهده جزئیات کلیک کنید' }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
