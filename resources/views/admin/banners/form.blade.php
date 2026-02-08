@extends('layouts.admin')

@section('title', $title ?? 'بنر')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'بنر' }}</h1>
                    <p class="page-subtitle">اطلاعات بنر را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.banners.index') }}">بازگشت</a>
                </div>
            </div>

            @php($banner = $banner ?? null)
            @php($isEdit = $banner && $banner->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.banners.update', $banner->id) : route('admin.banners.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">جایگاه</span>
                        <input name="position" required value="{{ old('position', (string) ($banner->position ?? 'home')) }}">
                        @error('position')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" value="{{ old('title', (string) ($banner->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">Media ID تصویر</span>
                        <input type="number" name="image_media_id" min="1" max="2000000000"
                            value="{{ old('image_media_id', (string) ($banner->image_media_id ?? '')) }}">
                        @error('image_media_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">لینک</span>
                        <input name="link_url" value="{{ old('link_url', (string) ($banner->link_url ?? '')) }}">
                        @error('link_url')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">شروع</span>
                            <input name="starts_at" value="{{ old('starts_at', $banner?->starts_at ? $banner->starts_at->format('Y-m-d H:i') : '') }}">
                            @error('starts_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">پایان</span>
                            <input name="ends_at" value="{{ old('ends_at', $banner?->ends_at ? $banner->ends_at->format('Y-m-d H:i') : '') }}">
                            @error('ends_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">ترتیب</span>
                            <input type="number" name="sort_order" min="0" max="1000000"
                                value="{{ old('sort_order', (string) ($banner->sort_order ?? 0)) }}">
                            @error('sort_order')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            <label class="cluster">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $banner?->is_active ? '1' : '') === '1')>
                                <span>فعال</span>
                            </label>
                            @error('is_active')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.banners.destroy', $banner->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف بنر</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
