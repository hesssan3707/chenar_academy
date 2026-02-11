@extends('layouts.admin')

@section('title', $title ?? 'مقاله')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'مقاله' }}</h1>
                    <p class="page-subtitle">اطلاعات پایه مقاله را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.posts.index') }}">بازگشت</a>
                </div>
            </div>

            @php($post = $post ?? null)
            @php($isEdit = $post && $post->exists)

            <div class="panel">
                <form method="post" action="{{ $isEdit ? route('admin.posts.update', $post->id) : route('admin.posts.store') }}"
                    class="stack stack--sm"
                    id="post-form"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">عنوان</span>
                            <input name="title" required value="{{ old('title', (string) ($post->title ?? '')) }}">
                            @error('title')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = (string) old('status', (string) ($post->status ?? 'draft')))
                            <select name="status" required>
                                <option value="draft" @selected($statusValue === 'draft')>پیش‌نویس</option>
                                <option value="published" @selected($statusValue === 'published')>منتشر شده</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">خلاصه</span>
                        <textarea name="excerpt">{{ old('excerpt', (string) ($post->excerpt ?? '')) }}</textarea>
                        @error('excerpt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">تصویر کاور</span>
                        @if (($post?->cover_media_id ?? null))
                            <div class="post-cover-preview">
                                <img src="{{ route('admin.media.stream', (int) $post->cover_media_id) }}" alt="">
                            </div>
                            <div class="field__hint">کاور فعلی. برای جایگزینی، تصویر جدید انتخاب کنید.</div>
                        @endif
                        <input type="file" name="cover_image" accept="image/*">
                        @error('cover_image')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">متن مقاله</span>
                        <textarea name="body" required data-wysiwyg="1"
                            data-wysiwyg-upload-url="{{ route('admin.media.wysiwyg') }}">{{ old('body', (string) ($post->body ?? '')) }}</textarea>
                        @error('body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">تاریخ انتشار</span>
                        @php($publishedAtValue = old('published_at', $post?->published_at ? jdate($post->published_at)->format('Y/m/d H:i') : ''))
                        <input name="published_at" data-jdp value="{{ $publishedAtValue }}">
                        @error('published_at')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="post-form">ذخیره</button>
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="post-delete-form">حذف مقاله</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.posts.destroy', $post->id) }}"
                        id="post-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف مقاله"
                        data-confirm-message="آیا از حذف این مقاله مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>

    <script src="https://cdn.ckeditor.com/4.22.1/full-all/ckeditor.js"></script>
@endsection
