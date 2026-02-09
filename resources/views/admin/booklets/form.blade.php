@extends('layouts.admin')

@section('title', $title ?? 'جزوه')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'جزوه' }}</h1>
                    <p class="page-subtitle">اطلاعات پایه جزوه را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.booklets.index') }}">بازگشت</a>
                </div>
            </div>

            @php($booklet = $booklet ?? null)
            @php($isEdit = $booklet && $booklet->exists)
            @php($requirePdf = ! $isEdit)

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.booklets.update', $booklet->id) : route('admin.booklets.store') }}"
                    enctype="multipart/form-data"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">عنوان</span>
                            <input name="title" required value="{{ old('title', (string) ($booklet->title ?? '')) }}">
                            @error('title')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = (string) old('status', (string) ($booklet->status ?? 'draft')))
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
                            <span class="field__label">فایل PDF جزوه</span>
                            <input type="file" name="booklet_file" accept="application/pdf" @required($requirePdf)>
                            @error('booklet_file')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">خلاصه</span>
                        <textarea name="excerpt">{{ old('excerpt', (string) ($booklet->excerpt ?? '')) }}</textarea>
                        @error('excerpt')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قیمت پایه</span>
                            <input type="number" name="base_price" min="0" max="2000000000"
                                value="{{ old('base_price', (string) ($booklet->base_price ?? 0)) }}">
                            @error('base_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">قیمت فروش</span>
                            <input type="number" name="sale_price" min="0" max="2000000000"
                                value="{{ old('sale_price', (string) ($booklet->sale_price ?? '')) }}">
                            @error('sale_price')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع تخفیف</span>
                            @php($discountTypeValue = old('discount_type', (string) ($booklet->discount_type ?? '')))
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
                            <input type="number" name="discount_value" min="0" max="2000000000"
                                value="{{ old('discount_value', (string) ($booklet->discount_value ?? '')) }}">
                            @error('discount_value')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">تاریخ انتشار</span>
                        @php($publishedAtValue = old('published_at', $booklet?->published_at ? jdate($booklet->published_at)->format('Y/m/d H:i') : ''))
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
                    <form method="post" action="{{ route('admin.booklets.destroy', $booklet->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف جزوه</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
