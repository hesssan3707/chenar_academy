@extends('layouts.app')

@section('title', 'وبلاگ')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">وبلاگ</h1>
            <p class="page-subtitle">آخرین مقالات و مطالب آموزشی</p>

            @if (($posts ?? collect())->isEmpty())
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle" style="margin: 0;">در حال حاضر مقاله‌ای منتشر نشده است.</p>
                </div>
            @else
                <div class="grid grid--3" style="margin-top: 18px;">
                    @foreach ($posts as $post)
                        <a class="card post" href="{{ route('blog.show', $post->slug) }}">
                            <div class="post__title">{{ $post->title }}</div>
                            <div class="post__date">{{ $post->published_at?->format('Y-m-d') }}</div>
                            <div class="post__excerpt">{{ $post->excerpt ?? '' }}</div>
                            <div class="card__action">ادامه مطلب</div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
