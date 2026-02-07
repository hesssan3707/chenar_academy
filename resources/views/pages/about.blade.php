@extends('layouts.app')

@section('title', 'درباره چنار آکادمی')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $about['title'] ?? 'درباره چنار آکادمی' }}</h1>
            <p class="page-subtitle">{{ $about['subtitle'] ?? '' }}</p>

            <div class="panel max-w-md">
                <div class="stack stack--sm">
                    @php($body = trim((string) ($about['body'] ?? '')))
                    @foreach (preg_split("/\\n\\s*\\n/", $body) as $paragraph)
                        @php($paragraphText = trim((string) $paragraph))
                        @if ($paragraphText !== '')
                            <p style="margin: 0;">{{ $paragraphText }}</p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
