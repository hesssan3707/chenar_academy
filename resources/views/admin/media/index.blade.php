@extends('layouts.admin')

@section('title', $title ?? 'رسانه‌ها')

@section('content')
    <section class="section">
        <div class="container">
            @php($isPicker = request()->boolean('picker'))
            @php($pickerField = (string) request('field', ''))

            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'رسانه‌ها' }}</h1>
                    <p class="page-subtitle">
                        {{ $isPicker ? 'برای انتخاب، روی یک تصویر کلیک کنید.' : 'مدیریت فایل‌های رسانه' }}
                    </p>
                </div>
                <div class="admin-page-header__actions">
                    @if (! $isPicker)
                        <a class="btn btn--primary" href="{{ route('admin.media.create') }}">آپلود رسانه</a>
                    @endif
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
                        @php($isPublic = (string) ($media->disk ?? '') === 'public')
                        @php($canPreview = (string) ($media->path ?? '') !== '')
                        @php($url = $canPreview ? route('admin.media.stream', $media->id) : null)

                        <div class="media-item panel">
                            <a class="media-item__thumb"
                                href="{{ $isPicker ? '#' : route('admin.media.show', $media->id) }}"
                                @if ($isPicker)
                                    data-picker-media-id="{{ $media->id }}"
                                    data-picker-field="{{ $pickerField }}"
                                    data-picker-is-image="{{ $isImage ? '1' : '0' }}"
                                    data-picker-is-public="{{ $isPublic ? '1' : '0' }}"
                                @endif
                            >
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
                                @if ($isPicker)
                                    @if ($isImage && $isPublic)
                                        <button class="btn btn--primary btn--sm" type="button"
                                            data-picker-select="{{ $media->id }}"
                                            data-picker-field="{{ $pickerField }}">انتخاب</button>
                                    @else
                                        <span class="badge">{{ $isImage ? 'فقط public' : 'فقط تصویر' }}</span>
                                    @endif
                                @else
                                    <form method="post" action="{{ route('admin.media.destroy', $media->id) }}" data-confirm="1">
                                        @csrf
                                        @method('delete')
                                        <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                    </form>
                                @endif
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

    @if ($isPicker)
        <script>
            (function() {
                const sendPick = (mediaId, field) => {
                    if (!mediaId || !field) return;
                    try {
                        window.parent.postMessage({
                            type: 'admin-media-picked',
                            mediaId: String(mediaId),
                            field: String(field),
                        }, window.location.origin);
                    } catch (e) {}
                };

                document.querySelectorAll('[data-picker-select]').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const mediaId = btn.getAttribute('data-picker-select');
                        const field = btn.getAttribute('data-picker-field');
                        sendPick(mediaId, field);
                    });
                });

                document.querySelectorAll('[data-picker-media-id]').forEach((a) => {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        const mediaId = a.getAttribute('data-picker-media-id');
                        const field = a.getAttribute('data-picker-field');
                        const isImage = a.getAttribute('data-picker-is-image') === '1';
                        const isPublic = a.getAttribute('data-picker-is-public') === '1';
                        if (!isImage || !isPublic) return;
                        sendPick(mediaId, field);
                    });
                });
            })();
        </script>
    @endif
@endsection
