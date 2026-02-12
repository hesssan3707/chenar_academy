@extends('layouts.spa')

@section('title', ($post->title ?? 'جزئیات مقاله'))

@section('content')
    @php($placeholderThumb = asset('images/default_image.webp'))
    @php($coverUrl = ($post->coverMedia?->disk ?? null) === 'public' && ($post->coverMedia?->path ?? null) ? Storage::disk('public')->url($post->coverMedia->path) : $placeholderThumb)
    @php($publishedAtLabel = $post->published_at ? jdate($post->published_at)->format('Y/m/d') : null)

    <div class="detail-shell">
        <div class="detail-header">
            <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ route('blog.index') }}">← بازگشت به وبلاگ</a>
            <div class="text-muted text-sm">{{ $publishedAtLabel ?? '' }}</div>
        </div>

        <div class="detail-grid detail-grid--2">
            <div class="detail-col">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 bg-black/20">
                        <h1 class="h2">{{ $post->title }}</h1>
                        @if (($post->excerpt ?? '') !== '')
                            <div class="text-white/80 mt-3">{{ $post->excerpt }}</div>
                        @endif
                    </div>

                    <div class="p-6 detail-scroll">
                        @php($body = trim((string) ($post->body ?? '')))
                        @if ($body !== '')
                            <div class="text-white/80 leading-loose space-y-4 rich-content">
                                {!! $body !!}
                            </div>
                        @else
                            @foreach (($blocks ?? collect()) as $block)
                                @php($text = trim((string) ($block->text ?? '')))
                                @if ($block->block_type === 'heading' && $text !== '')
                                    <h2 class="h3 mt-8 mb-4 text-brand">{{ $text }}</h2>
                                @elseif ($text !== '')
                                    <div class="text-white/80 leading-loose space-y-4">
                                        @foreach (preg_split("/\\n\\s*\\n/", $text) as $paragraph)
                                            @php($paragraphText = trim((string) $paragraph))
                                            @if ($paragraphText !== '')
                                                <p>{{ $paragraphText }}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <div class="detail-col">
                <div class="detail-card panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10 bg-black/10">
                        <div class="h4">جزئیات</div>
                    </div>

                    <div class="p-6 detail-scroll">
                        <div class="spa-cover mb-6">
                            <img src="{{ $coverUrl }}" alt="{{ $post->title }}" loading="lazy" onerror="this.onerror=null;this.src='{{ $placeholderThumb }}';">
                        </div>

                        @if ($publishedAtLabel)
                            <div class="flex items-center justify-between gap-3 text-sm text-white/80">
                                <div class="text-muted">انتشار</div>
                                <div class="font-bold text-white/90">{{ $publishedAtLabel }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
