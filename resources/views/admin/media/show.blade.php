@extends('layouts.admin')

@section('title', $title ?? 'نمایش رسانه')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نمایش رسانه' }}</h1>
                    <p class="page-subtitle">جزئیات رسانه</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.media.index') }}">بازگشت</a>
                </div>
            </div>

            @php($mediaUrl = $mediaUrl ?? null)
            @php($mime = strtolower((string) ($media->mime_type ?? '')))

            <div class="grid admin-grid-2">
                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="field__label">پیش‌نمایش</div>

                        @if ($mediaUrl && str_starts_with($mime, 'image/'))
                            <img src="{{ $mediaUrl }}" alt=""
                                style="width: 100%; max-height: 420px; object-fit: contain; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);"
                                loading="lazy">
                        @elseif ($mediaUrl && str_starts_with($mime, 'video/'))
                            <video controls preload="metadata"
                                style="width: 100%; border-radius: 14px; border: 1px solid var(--border); background: rgba(0,0,0,0.2);">
                                <source src="{{ $mediaUrl }}" type="{{ $mime ?: 'video/mp4' }}">
                            </video>
                        @elseif ($mediaUrl && str_starts_with($mime, 'audio/'))
                            <audio controls preload="metadata" style="width: 100%;">
                                <source src="{{ $mediaUrl }}" type="{{ $mime ?: 'audio/mpeg' }}">
                            </audio>
                        @elseif ($mediaUrl && $mime === 'application/pdf')
                            <iframe src="{{ $mediaUrl }}" style="width: 100%; height: 520px; border-radius: 14px; border: 1px solid var(--border);"></iframe>
                        @elseif ($mediaUrl)
                            <a class="btn btn--ghost" href="{{ $mediaUrl }}" target="_blank" rel="noreferrer">باز کردن فایل</a>
                        @else
                            <div class="card__meta">فایل برای نمایش در دسترس نیست.</div>
                        @endif
                    </div>
                </div>

                <div class="panel">
                    <div class="stack stack--xs">
                        <div class="field__label">اطلاعات</div>
                        <div>شناسه: {{ $media->id }}</div>
                        <div>Disk: {{ $media->disk }}</div>
                        <div>Path: {{ $media->path }}</div>
                        @if ((string) ($media->path ?? '') !== '')
                            <div>
                                Admin URL:
                                <a class="link" href="{{ route('admin.media.stream', $media->id) }}" target="_blank" rel="noreferrer">
                                    {{ route('admin.media.stream', $media->id) }}
                                </a>
                            </div>
                            @if ((string) ($media->disk ?? '') === 'public')
                                <div>
                                    Public URL:
                                    <a class="link" href="{{ route('media.stream', $media->id) }}" target="_blank" rel="noreferrer">
                                        {{ route('media.stream', $media->id) }}
                                    </a>
                                </div>
                            @endif
                        @endif
                        <div>Original: {{ $media->original_name ?? '—' }}</div>
                        <div>MIME: {{ $media->mime_type ?? '—' }}</div>
                        <div>Size: {{ $media->size ? number_format((int) $media->size) : '—' }}</div>
                        @if ($media->duration_seconds)
                            <div>Duration: {{ (int) $media->duration_seconds }}s</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
