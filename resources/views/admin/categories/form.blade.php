@extends('layouts.admin')

@section('title', $title ?? 'دسته‌بندی')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دسته‌بندی' }}</h1>
                    <p class="page-subtitle">اطلاعات دسته‌بندی را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.categories.index') }}">بازگشت</a>
                </div>
            </div>

            @php($category = $category ?? null)
            @php($isEdit = $category && $category->exists)
            @php($relatedCount = (int) ($category?->related_count ?? 0))
            @php($typeKeyValue = (string) ($category?->type ?? ''))
            @php($isProductType = in_array($typeKeyValue, ['note', 'video'], true))
            @php($canDelete = $isEdit && (! $isProductType || $relatedCount === 0))
            @php($parents = $parents ?? collect())
            @php($categoryTypes = $categoryTypes ?? collect())
            @php($typeTitles = $typeTitles ?? [])

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}"
                    class="stack stack--sm"
                    id="category-form"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع</span>
                            @php($typeIdValue = old('category_type_id', (string) ($category->category_type_id ?? '')))
                            <select name="category_type_id" required data-category-type>
                                <option value="" @selected($typeIdValue === '')>—</option>
                                @foreach ($categoryTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) $type->id === (string) $typeIdValue)>{{ $type->title ?? $type->key }}</option>
                                @endforeach
                            </select>
                            @error('category_type_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">عنوان</span>
                            <input name="title" required value="{{ old('title', (string) ($category->title ?? '')) }}">
                            @error('title')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">والد</span>
                        @php($parentValue = old('parent_id', (string) ($category->parent_id ?? '')))
                        <select name="parent_id" data-category-parent>
                            <option value="">—</option>
                            @foreach ($parents as $parent)
                                @php($parentTypeKey = (string) ($parent->type ?? ''))
                                <option value="{{ $parent->id }}" data-type="{{ (string) ($parent->category_type_id ?? '') }}" @selected((string) $parent->id === (string) $parentValue)>
                                    {{ $typeTitles[$parentTypeKey] ?? $parentTypeKey }} — {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <textarea name="description">{{ old('description', (string) ($category->description ?? '')) }}</textarea>
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">کاور</span>
                        <input type="file" name="cover_image" accept="image/*">
                        @if (($category?->coverMedia?->disk ?? null) === 'public' && ($category?->coverMedia?->path ?? null))
                            <div class="mt-2">
                                @php($coverUrl = Storage::disk('public')->url($category->coverMedia->path))
                                <button type="button"
                                    style="all: unset; cursor: zoom-in; display: inline-block;"
                                    data-media-preview-src="{{ $coverUrl }}"
                                    data-media-preview-type="image"
                                    data-media-preview-label="پیش‌نمایش کاور دسته‌بندی">
                                    <img src="{{ $coverUrl }}" alt="{{ $category->title }}" style="width: 220px; height: 120px; object-fit: cover; border-radius: 10px; display: block;">
                                </button>
                            </div>
                            <label class="cluster mt-2">
                                <input type="hidden" name="remove_cover_image" value="0">
                                <input type="checkbox" name="remove_cover_image" value="1" @checked(old('remove_cover_image') === '1')>
                                <span>حذف کاور فعلی</span>
                            </label>
                        @endif
                        @error('cover_image')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">ترتیب</span>
                            <input type="number" name="sort_order" min="0" max="1000000"
                                value="{{ old('sort_order', (string) ($category->sort_order ?? 0)) }}">
                            @error('sort_order')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            <label class="cluster">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ? '1' : '') === '1')>
                                <span>فعال</span>
                            </label>
                            @error('is_active')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="category-form">ذخیره</button>
                    @if ($canDelete)
                        <button class="btn btn--danger" type="submit" form="category-delete-form">حذف دسته‌بندی</button>
                    @endif
                </div>

                @if ($canDelete)
                    <form method="post"
                        action="{{ route('admin.categories.destroy', $category->id) }}"
                        id="category-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف دسته‌بندی"
                        data-confirm-message="آیا از حذف این دسته‌بندی مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
