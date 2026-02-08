@extends('layouts.admin')

@section('title', $title ?? 'دوره')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دوره' }}</h1>
                    <p class="page-subtitle">اطلاعات دوره را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.courses.index') }}">بازگشت</a>
                </div>
            </div>

            @php($courseProduct = $courseProduct ?? null)
            @php($course = $course ?? null)
            @php($isEdit = $courseProduct && $courseProduct->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.courses.update', $courseProduct->id) : route('admin.courses.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" required value="{{ old('title', (string) ($courseProduct->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">اسلاگ</span>
                        <input name="slug" required value="{{ old('slug', (string) ($courseProduct->slug ?? '')) }}">
                        @error('slug')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = old('status', (string) ($courseProduct->status ?? 'draft')))
                            <select name="status" required>
                                <option value="draft" @selected($statusValue === 'draft')>draft</option>
                                <option value="published" @selected($statusValue === 'published')>published</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">زمان انتشار</span>
                            <input name="published_at" value="{{ old('published_at', $courseProduct?->published_at ? $courseProduct->published_at->format('Y-m-d H:i') : '') }}">
                            @error('published_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">خلاصه</span>
                        <textarea name="excerpt">{{ old('excerpt', (string) ($courseProduct->excerpt ?? '')) }}</textarea>
                        @error('excerpt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <textarea name="description">{{ old('description', (string) ($courseProduct->description ?? '')) }}</textarea>
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قیمت</span>
                            <input type="number" name="base_price" required min="0" max="2000000000"
                                value="{{ old('base_price', (string) ($courseProduct->base_price ?? 0)) }}">
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">ارز</span>
                            <input name="currency" required value="{{ old('currency', (string) ($courseProduct->currency ?? 'IRR')) }}">
                            @error('currency')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">سطح</span>
                            <input name="level" value="{{ old('level', (string) ($course->level ?? '')) }}">
                            @error('level')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">مدت (ثانیه)</span>
                            <input type="number" name="total_duration_seconds" min="0" max="2000000000"
                                value="{{ old('total_duration_seconds', (string) ($course->total_duration_seconds ?? '')) }}">
                            @error('total_duration_seconds')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">محتوا</span>
                        <textarea name="body">{{ old('body', (string) ($course->body ?? '')) }}</textarea>
                        @error('body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.courses.destroy', $courseProduct->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف دوره</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
