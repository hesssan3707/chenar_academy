@extends('layouts.spa')

@section('title', 'وبلاگ')

@section('content')
    <div class="w-full h-full flex flex-col justify-center max-w-7xl mx-auto">
        <div class="mb-6 text-center">
            <h1 class="h2 text-white">وبلاگ</h1>
            <p class="text-muted">آخرین مقالات و مطالب آموزشی</p>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 h-full">
            @if (($posts ?? collect())->isEmpty())
                <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                    <p class="text-muted">در حال حاضر مقاله‌ای منتشر نشده است.</p>
                </div>
            @else
                <div class="h-scroll-container">
                    @foreach ($posts as $post)
                        <a href="{{ route('blog.show', $post->slug) }}" class="card-product">
                            @php($placeholderThumb = asset('images/default_image.webp'))
                            @php($coverUrl = ($post->coverMedia?->disk ?? null) === 'public' && ($post->coverMedia?->path ?? null) ? Storage::disk('public')->url($post->coverMedia->path) : $placeholderThumb)
                            <div class="spa-cover mb-4">
                                <img src="{{ $coverUrl }}" alt="{{ $post->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                            </div>
                            
                            <h3 class="font-bold text-lg mb-2 truncate">{{ $post->title }}</h3>
                            
                            <div class="text-sm text-muted mb-4 line-clamp-2">
                                {{ $post->excerpt ?? '' }}
                            </div>

                            <div class="mt-auto flex justify-between items-center">
                                <span class="text-xs text-muted">{{ $post->published_at ? jdate($post->published_at)->format('Y/m/d') : '' }}</span>
                                <span class="btn btn--ghost btn--sm">ادامه مطلب</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
