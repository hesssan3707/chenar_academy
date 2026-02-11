@extends('layouts.spa')

@section('title', ($post->title ?? 'جزئیات مقاله'))

@section('content')
    <div class="container h-full flex flex-col justify-center py-6">
        <div class="mb-4">
             <a class="btn btn--ghost btn--sm text-white/70 hover:text-white" href="{{ route('blog.index') }}">
                ← بازگشت به وبلاگ
            </a>
        </div>

        <div class="panel p-0 bg-white/5 border border-white/10 rounded-2xl overflow-hidden h-full max-h-[80vh] flex flex-col">
            <div class="p-6 border-b border-white/10 bg-black/20">
                <h1 class="h2 mb-2">{{ $post->title }}</h1>
                <div class="text-sm text-muted">{{ $post->published_at ? jdate($post->published_at)->format('Y/m/d') : '' }}</div>
            </div>

            <div class="p-8 flex-1 overflow-y-auto custom-scrollbar">
                <div class="max-w-3xl mx-auto space-y-6">
                    @if (($post->excerpt ?? '') !== '')
                        <div class="text-xl text-white/90 font-light leading-relaxed border-l-4 border-brand pl-4">
                            {{ $post->excerpt }}
                        </div>
                    @endif

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
    </div>
@endsection
