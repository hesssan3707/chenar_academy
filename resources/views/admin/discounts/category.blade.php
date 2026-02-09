@extends('layouts.admin')

@section('title', $title ?? 'تخفیف گروهی محصولات')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'تخفیف گروهی محصولات' }}</h1>
                    <p class="page-subtitle">اعمال تخفیف درصدی یا مبلغ ثابت روی محصولات یک دسته‌بندی</p>
                </div>
            </div>

            @php($categories = $categories ?? collect())

            <div class="panel max-w-md">
                <form method="post" action="{{ route('admin.discounts.category.apply') }}" class="stack stack--sm">
                    @csrf

                    <label class="field">
                        <span class="field__label">دسته‌بندی</span>
                        @php($categoryValue = (string) old('category_id', ''))
                        <select name="category_id" required>
                            <option value="" disabled @selected($categoryValue === '')>انتخاب کنید</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($categoryValue === (string) $category->id)>
                                    {{ $category->title }} ({{ $category->type }})
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع تخفیف</span>
                            @php($typeValue = (string) old('discount_type', 'percent'))
                            <select name="discount_type" required>
                                <option value="percent" @selected($typeValue === 'percent')>percent</option>
                                <option value="amount" @selected($typeValue === 'amount')>amount</option>
                            </select>
                            @error('discount_type')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">مقدار</span>
                            <input type="number" name="discount_value" required min="0" max="2000000000"
                                value="{{ old('discount_value', '0') }}">
                            @error('discount_value')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">اعمال تخفیف</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

