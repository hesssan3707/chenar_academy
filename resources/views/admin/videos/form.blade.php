@extends('layouts.admin')

@section('title', $title ?? 'ویدیو')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'ویدیو' }}</h1>
                    <p class="page-subtitle">اطلاعات پایه ویدیو را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.videos.index') }}">بازگشت</a>
                </div>
            </div>

            @php($videoProduct = $videoProduct ?? null)
            @php($video = $video ?? null)
            @php($isEdit = $videoProduct && $videoProduct->exists)

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.videos.update', $videoProduct->id) : route('admin.videos.store') }}"
                    enctype="multipart/form-data"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">عنوان</span>
                            <input name="title" required value="{{ old('title', (string) ($videoProduct->title ?? '')) }}">
                            @error('title')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = (string) old('status', (string) ($videoProduct->status ?? 'draft')))
                            <select name="status" required>
                                <option value="draft" @selected($statusValue === 'draft')>پیش‌نویس</option>
                                <option value="published" @selected($statusValue === 'published')>منتشر شده</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">کاور (تصویر)</span>
                            <input type="file" name="cover_image" accept="image/*">
                            @error('cover_image')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">پیش‌نمایش (ویدیو کوتاه)</span>
                            <input type="file" name="preview_video" accept="video/*">
                            @error('preview_video')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">فایل ویدیو (کامل)</span>
                        <input type="file" name="video_file" accept="video/*">
                        @error('video_file')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">خلاصه</span>
                        <textarea name="excerpt">{{ old('excerpt', (string) ($videoProduct->excerpt ?? '')) }}</textarea>
                        @error('excerpt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قیمت پایه</span>
                            <input type="number" name="base_price" min="0" max="2000000000"
                                value="{{ old('base_price', (string) ($videoProduct->base_price ?? 0)) }}">
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">قیمت فروش</span>
                            <input type="number" name="sale_price" min="0" max="2000000000"
                                value="{{ old('sale_price', (string) ($videoProduct->sale_price ?? '')) }}">
                            @error('sale_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">تاریخ انتشار</span>
                        @php($publishedAtValue = old('published_at', $videoProduct?->published_at ? jdate($videoProduct->published_at)->format('Y/m/d H:i') : ''))
                        <input name="published_at" data-jdp value="{{ $publishedAtValue }}">
                        @error('published_at')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.videos.destroy', $videoProduct->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف ویدیو</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
