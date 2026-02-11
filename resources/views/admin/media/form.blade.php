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
                <form method="post" action="{{ route('admin.media.store') }}" class="stack stack--sm" enctype="multipart/form-data">
                    @csrf

                    <label class="field">
                        <span class="field__label">Disk</span>
                        @php($diskValue = (string) old('disk', 'public'))
                        <select name="disk" required>
                            <option value="public" @selected($diskValue === 'public')>public</option>
                            <option value="local" @selected($diskValue === 'local')>local</option>
                        </select>
                        @error('disk')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">File</span>
                        <input type="file" name="file" required>
                        @error('file')
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
