@extends('layouts.app')

@section('title', ($post->title ?? 'جزئیات مقاله'))

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $post->title }}</h1>
            <p class="page-subtitle">{{ $post->published_at?->format('Y-m-d') }}</p>

            <div class="panel max-w-md">
                <div class="stack stack--sm">
                    @if (($post->excerpt ?? '') !== '')
                        <p style="margin: 0; color: var(--muted);">{{ $post->excerpt }}</p>
                    @endif

                    @foreach (($blocks ?? collect()) as $block)
                        @php($text = trim((string) ($block->text ?? '')))
                        @if ($block->block_type === 'heading' && $text !== '')
                            <h2 class="section__title" style="font-size: 18px;">{{ $text }}</h2>
                        @elseif ($text !== '')
                            @foreach (preg_split("/\\n\\s*\\n/", $text) as $paragraph)
                                @php($paragraphText = trim((string) $paragraph))
                                @if ($paragraphText !== '')
                                    <p style="margin: 0;">{{ $paragraphText }}</p>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="form-actions" style="margin-top: 18px;">
                <a class="btn btn--ghost" href="{{ route('blog.index') }}">بازگشت به وبلاگ</a>
            </div>
        </div>
    </section>
@endsection
