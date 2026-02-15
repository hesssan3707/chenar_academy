@extends('layouts.admin')

@section('title', $title ?? 'محصول')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'محصول' }}</h1>
                    <p class="page-subtitle">اطلاعات محصول را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.products.index') }}">بازگشت</a>
                </div>
            </div>

            @php($product = $product ?? null)
            @php($isEdit = $product && $product->exists)
            @php($institutions = $institutions ?? collect())
            @php($categories = $categories ?? collect())

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
                    class="stack stack--sm"
                    id="product-form"
                    data-discount-unit-form
                    data-currency-unit="{{ $commerceCurrencyLabel ?? 'ریال' }}">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع</span>
                            @php($typeValue = old('type', (string) ($product->type ?? 'note')))
                            <select name="type" required>
                                <option value="note" @selected($typeValue === 'note')>note</option>
                                <option value="course" @selected($typeValue === 'course')>course</option>
                                <option value="other" @selected($typeValue === 'other')>other</option>
                            </select>
                            @error('type')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = old('status', (string) ($product->status ?? 'draft')))
                            <select name="status" required>
                                <option value="draft" @selected($statusValue === 'draft')>draft</option>
                                <option value="published" @selected($statusValue === 'published')>published</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع دانشگاه</span>
                            @php($institutionValue = old('institution_category_id', (string) ($product->institution_category_id ?? '')))
                            <select name="institution_category_id" required>
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
                            @php($productTypeValue = (string) ($product->type ?? ''))
                            @php($fallbackCategoryId = $isEdit ? ($product?->categories()->whereIn('type', ['note', 'video', 'course'])->value('categories.id') ?? '') : '')
                            @php($currentCategoryId = $isEdit && in_array($productTypeValue, ['note', 'video', 'course'], true) ? ($product?->categories()->where('type', $productTypeValue)->value('categories.id') ?? $fallbackCategoryId) : $fallbackCategoryId)
                            @php($categoryValue = old('category_id', (string) ($currentCategoryId ?? '')))
                            @php($typeLabels = ['note' => 'جزوه', 'video' => 'ویدیو', 'course' => 'دوره'])
                            <select name="category_id" required>
                                <option value="" @selected($categoryValue === '')>—</option>
                                @foreach ($categories as $category)
                                    @php($catType = (string) ($category->type ?? ''))
                                    <option value="{{ $category->id }}" @selected((string) $category->id === (string) $categoryValue)>
                                        {{ ($typeLabels[$catType] ?? $catType) !== '' ? (($typeLabels[$catType] ?? $catType).' - ') : '' }}{{ $category->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" required value="{{ old('title', (string) ($product->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">خلاصه</span>
                        <textarea name="excerpt">{{ old('excerpt', (string) ($product->excerpt ?? '')) }}</textarea>
                        @error('excerpt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <textarea name="description">{{ old('description', (string) ($product->description ?? '')) }}</textarea>
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قیمت</span>
                            <div class="input-group">
                                <input type="number" name="base_price" required min="0" max="2000000000"
                                    value="{{ old('base_price', (string) ($product->base_price ?? 0)) }}">
                                <span class="card__meta">{{ $commerceCurrencyLabel ?? 'ریال' }}</span>
                            </div>
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">قیمت فروش</span>
                            <div class="input-group">
                                <input type="number" name="sale_price" min="0" max="2000000000"
                                    value="{{ old('sale_price', (string) ($product->sale_price ?? '')) }}">
                                <span class="card__meta">{{ $commerceCurrencyLabel ?? 'ریال' }}</span>
                            </div>
                            @error('sale_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع تخفیف</span>
                            @php($discountTypeValue = old('discount_type', (string) ($product->discount_type ?? '')))
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
                                    value="{{ old('discount_value', (string) ($product->discount_value ?? '')) }}">
                                <span class="card__meta" data-discount-unit>{{ $discountTypeValue === 'percent' ? '٪' : ($commerceCurrencyLabel ?? 'ریال') }}</span>
                            </div>
                            @error('discount_value')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">زمان انتشار</span>
                            <input name="published_at" data-jdp value="{{ old('published_at', $product?->published_at ? jdate($product->published_at)->format('Y/m/d H:i') : '') }}">
                            @error('published_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>
                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="product-form">ذخیره</button>
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="product-delete-form">حذف محصول</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.products.destroy', $product->id) }}"
                        id="product-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف محصول"
                        data-confirm-message="آیا از حذف این محصول مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
