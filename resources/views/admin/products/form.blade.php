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

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
                    class="stack stack--sm">
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
                            <input type="number" name="base_price" required min="0" max="2000000000"
                                value="{{ old('base_price', (string) ($product->base_price ?? 0)) }}">
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">قیمت فروش</span>
                            <input type="number" name="sale_price" min="0" max="2000000000"
                                value="{{ old('sale_price', (string) ($product->sale_price ?? '')) }}">
                            @error('sale_price')
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

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.products.destroy', $product->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف محصول</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
