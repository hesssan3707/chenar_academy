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
                    class="stack stack--sm">
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
                        <span class="field__label">تاریخ انتشار</span>
                        @php($publishedAtValue = old('published_at', $post?->published_at ? jdate($post->published_at)->format('Y/m/d H:i') : ''))
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
                    <form method="post" action="{{ route('admin.posts.destroy', $post->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف مقاله</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
