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
            @php($useOld = session()->hasOldInput() && $errors->any())

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}"
                    class="stack stack--sm"
                    id="product-form">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <h2 class="page-title">{{ $product->title ?? 'محصول' }}</h2>
                    <p class="text-muted">محصول حاضر را به یکی از انواع زیر تبدیل کنید.</p>

                    <label class="field">
                        <span class="field__label">نوع هدف</span>
                        @php($typeValue = old('type', (string) ($product->type ?? '')))
                        <select name="type" required>
                            <option value="note" @selected($typeValue === 'note')>جزوه</option>
                            <option value="video" @selected($typeValue === 'video')>ویدیو</option>
                            <option value="course" @selected($typeValue === 'course')>دوره</option>
                        </select>
                        @error('type')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>
                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="product-form">تبدیل و ویرایش</button>
                </div>
            </div>
        </div>
    </section>
@endsection
