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
            @php($parents = $parents ?? collect())

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.categories.update', $category->id) : route('admin.categories.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">نوع</span>
                        <input name="type" required value="{{ old('type', (string) ($category->type ?? '')) }}">
                        @error('type')
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

                    <label class="field">
                        <span class="field__label">اسلاگ</span>
                        <input name="slug" required value="{{ old('slug', (string) ($category->slug ?? '')) }}">
                        @error('slug')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">والد</span>
                        @php($parentValue = old('parent_id', (string) ($category->parent_id ?? '')))
                        <select name="parent_id">
                            <option value="">—</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected((string) $parent->id === (string) $parentValue)>
                                    {{ $parent->type }} — {{ $parent->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">کلید آیکن</span>
                        <input name="icon_key" value="{{ old('icon_key', (string) ($category->icon_key ?? '')) }}">
                        @error('icon_key')
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

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.categories.destroy', $category->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف دسته‌بندی</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
