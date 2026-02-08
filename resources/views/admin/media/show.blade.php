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
                    <a class="btn btn--primary" href="{{ route('admin.media.edit', $media->id) }}">ویرایش</a>
                </div>
            </div>

            <div class="panel max-w-md">
                <div class="stack stack--xs">
                    <div>شناسه: {{ $media->id }}</div>
                    <div>Disk: {{ $media->disk }}</div>
                    <div>Path: {{ $media->path }}</div>
                    <div>Original: {{ $media->original_name ?? '—' }}</div>
                    <div>MIME: {{ $media->mime_type ?? '—' }}</div>
                    <div>Size: {{ $media->size ? number_format((int) $media->size) : '—' }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection
