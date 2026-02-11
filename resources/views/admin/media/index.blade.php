@extends('layouts.admin')

@section('title', $title ?? 'رسانه‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'رسانه‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت فایل‌های رسانه</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.media.create') }}">آپلود رسانه</a>
                </div>
            </div>

            @php($mediaItems = $mediaItems ?? null)

            @if (! $mediaItems || $mediaItems->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز رسانه‌ای ثبت نشده است.</p>
                </div>
            @else
                <div class="media-grid">
                    @foreach ($mediaItems as $media)
                        @php($mime = strtolower((string) ($media->mime_type ?? '')))
                        @php($isImage = $mime !== '' && str_starts_with($mime, 'image/'))
                        @php($canPreview = (string) ($media->path ?? '') !== '')
                        @php($url = $canPreview ? route('admin.media.stream', $media->id) : null)

                        <div class="media-item panel">
                            <a class="media-item__thumb" href="{{ route('admin.media.show', $media->id) }}">
                                @if ($isImage && $url)
                                    <img src="{{ $url }}" alt="" loading="lazy">
                                @else
                                    <div class="media-item__icon">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="currentColor" stroke-width="1.5"/>
                                            <path d="M14 2v6h6" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </div>
                                @endif
                            </a>

                            <div class="media-item__meta">
                                <div class="media-item__name">
                                    <a class="link" href="{{ route('admin.media.show', $media->id) }}">
                                        {{ $media->original_name ?: $media->path }}
                                    </a>
                                </div>
                                <div class="media-item__sub">
                                    <span class="badge">{{ $media->disk }}</span>
                                    @if (($media->size ?? null) !== null)
                                        <span class="badge">{{ number_format((int) $media->size) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="media-item__actions">
                                <form method="post" action="{{ route('admin.media.destroy', $media->id) }}" data-confirm="1">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="admin-pagination">
                    {{ $mediaItems->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
