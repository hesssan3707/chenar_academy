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
            @php($products = $products ?? null)
            @php($currencyUnit = $currencyUnit ?? 'IRR')
            @php($displayCurrencyUnit = $currencyUnit === 'IRT' ? 'تومان' : 'ریال')

            <div class="grid admin-grid-2">
                <div class="panel panel--max-h-400">
                    <h2 class="section-title">اعمال روی دسته‌بندی</h2>
                    <form method="post" action="{{ route('admin.discounts.category.apply') }}" class="stack stack--sm" data-discount-unit-form data-currency-unit="{{ $displayCurrencyUnit }}">
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
                                @php($typeValue = (string) old('discount_type', 'percent'))
                                <span class="field__label">مقدار (<span data-discount-unit>{{ $typeValue === 'percent' ? '٪' : $displayCurrencyUnit }}</span>)</span>
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

                <div class="panel">
                    <h2 class="section-title">اعمال روی محصولات انتخاب‌شده</h2>
                    <form method="post" action="{{ route('admin.discounts.products.apply') }}" class="stack stack--sm" data-discount-unit-form data-currency-unit="{{ $displayCurrencyUnit }}">
                        @csrf

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
                                <span class="field__label">مقدار (<span data-discount-unit>{{ $typeValue === 'percent' ? '٪' : $displayCurrencyUnit }}</span>)</span>
                                <input type="number" name="discount_value" required min="0" max="2000000000"
                                    value="{{ old('discount_value', '0') }}">
                                @error('discount_value')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>
                        </div>

                        @error('product_ids')
                            <div class="field__error">{{ $message }}</div>
                        @enderror

                        @if (! $products || $products->count() === 0)
                            <div class="text-muted">محصولی برای نمایش وجود ندارد.</div>
                        @else
                            <div class="table-wrap table-wrap--max-h">
                                <table class="table table--sm table--compact table--fixed">
                                    <colgroup>
                                        <col class="admin-col-check">
                                        <col class="admin-col-type">
                                        <col>
                                        <col class="admin-col-discount">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>نوع</th>
                                            <th>عنوان</th>
                                            <th>تخفیف</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($selectedIds = collect(old('product_ids', []))->map(fn ($v) => (string) $v)->all())
                                        @foreach ($products as $product)
                                            <tr>
                                                <td class="admin-nowrap">
                                                    <input type="checkbox" name="product_ids[]"
                                                        value="{{ $product->id }}" @checked(in_array((string) $product->id, $selectedIds, true))>
                                                </td>
                                                <td class="admin-nowrap">{{ $product->type }}</td>
                                                <td>{{ $product->title }}</td>
                                                <td class="admin-nowrap">{{ $product->discountLabelFor($currencyUnit) ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="admin-pagination">
                                {{ $products->links() }}
                            </div>
                        @endif

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">اعمال تخفیف</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
