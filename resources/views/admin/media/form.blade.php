@extends('layouts.admin')

@section('title', $title ?? 'رسانه')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'رسانه' }}</h1>
                    <p class="page-subtitle">اطلاعات رسانه را ثبت کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.media.index') }}">بازگشت</a>
                    @if (($media ?? null) && $media->exists)
                        <a class="btn btn--ghost" href="{{ route('admin.media.show', $media->id) }}">نمایش</a>
                    @endif
                </div>
            </div>

            @php($media = $media ?? null)
            @php($isEdit = $media && $media->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.media.update', $media->id) : route('admin.media.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">Disk</span>
                        <input name="disk" required value="{{ old('disk', (string) ($media->disk ?? 'public')) }}">
                        @error('disk')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">Path</span>
                        <input name="path" required value="{{ old('path', (string) ($media->path ?? '')) }}">
                        @error('path')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">Original name</span>
                        <input name="original_name" value="{{ old('original_name', (string) ($media->original_name ?? '')) }}">
                        @error('original_name')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">MIME type</span>
                        <input name="mime_type" value="{{ old('mime_type', (string) ($media->mime_type ?? '')) }}">
                        @error('mime_type')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">Size</span>
                        <input type="number" name="size" min="0" max="9223372036854775807"
                            value="{{ old('size', (string) ($media->size ?? '')) }}">
                        @error('size')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
