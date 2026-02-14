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
            @php($institutions = $institutions ?? collect())
            @php($categories = $categories ?? collect())

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.videos.update', $videoProduct->id) : route('admin.videos.store') }}"
                    enctype="multipart/form-data"
                    class="stack stack--sm"
                    id="video-form"
                    data-discount-unit-form
                    data-currency-unit="{{ $commerceCurrencyLabel ?? 'ریال' }}">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" required value="{{ old('title', (string) ($videoProduct->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع دانشگاه</span>
                            @php($institutionValue = old('institution_category_id', (string) ($videoProduct->institution_category_id ?? '')))
                            <select name="institution_category_id">
                                <option value="" @selected($institutionValue === '')>—</option>
                                @foreach ($institutions as $institution)
                                    <option value="{{ $institution->id }}" @selected((string) $institution->id === (string) $institutionValue)>
                                        {{ $institution->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('institution_category_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">دسته‌بندی</span>
                            @php($categoryValue = old('category_id', (string) ($isEdit ? ($videoProduct?->categories()->where('type', 'video')->value('categories.id') ?? '') : '')))
                            <select name="category_id">
                                <option value="" @selected($categoryValue === '')>—</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $category->id === (string) $categoryValue)>
                                        {{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
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
                            <span class="field__label">قیمت</span>
                            <div class="input-group">
                                <input type="number" name="base_price" min="0" max="2000000000"
                                    value="{{ old('base_price', (string) ($videoProduct->base_price ?? 0)) }}">
                                <span class="card__meta">{{ $commerceCurrencyLabel ?? 'ریال' }}</span>
                            </div>
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع تخفیف</span>
                            @php($discountTypeValue = old('discount_type', (string) ($videoProduct->discount_type ?? '')))
                            <select name="discount_type">
                                <option value="" @selected($discountTypeValue === '')>—</option>
                                <option value="percent" @selected($discountTypeValue === 'percent')>percent</option>
                                <option value="amount" @selected($discountTypeValue === 'amount')>amount</option>
                            </select>
                            @error('discount_type')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">مقدار تخفیف</span>
                            <div class="input-group">
                                <input type="number" name="discount_value" min="0" max="2000000000"
                                    value="{{ old('discount_value', (string) ($videoProduct->discount_value ?? '')) }}">
                                <span class="card__meta" data-discount-unit>{{ $discountTypeValue === 'percent' ? '٪' : ($commerceCurrencyLabel ?? 'ریال') }}</span>
                            </div>
                            @error('discount_value')
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

                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" name="intent" value="save" form="video-form">ذخیره</button>
                    <button class="btn btn--ghost" type="submit" name="intent" value="publish" form="video-form">انتشار</button>
                    @if ($isEdit && (string) $videoProduct->status === 'published')
                        <button class="btn btn--ghost" type="submit" name="intent" value="draft" form="video-form">تبدیل به پیش‌نویس</button>
                    @endif
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="video-delete-form">حذف ویدیو</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.videos.destroy', $videoProduct->id) }}"
                        id="video-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف ویدیو"
                        data-confirm-message="آیا از حذف این ویدیو مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
