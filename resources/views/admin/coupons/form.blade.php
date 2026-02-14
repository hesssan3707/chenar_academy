@extends('layouts.admin')

@section('title', $title ?? 'کد تخفیف')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'کد تخفیف' }}</h1>
                    <p class="page-subtitle">اطلاعات کد تخفیف را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.coupons.index') }}">بازگشت</a>
                </div>
            </div>

            @php($coupon = $coupon ?? null)
            @php($isEdit = $coupon && $coupon->exists)
            @php($products = $products ?? collect())
            @php($selectedProductIds = $selectedProductIds ?? [])
            @php($oldSelected = collect(old('product_ids', $selectedProductIds))->map(fn ($id) => (int) $id)->filter(fn ($id) => $id > 0)->unique()->values()->all())
            @php($applyAll = old('apply_all_products', count($oldSelected) === 0 ? '1' : '0') === '1')

            <div class="panel">
                <form method="post"
                    action="{{ $isEdit ? route('admin.coupons.update', $coupon->id) : route('admin.coupons.store') }}"
                    class="stack stack--sm"
                    id="coupon-form">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">کد</span>
                        <div class="input-group">
                            <input name="code" required minlength="5" maxlength="8" value="{{ old('code', (string) ($coupon->code ?? '')) }}" data-coupon-code-input>
                            <button class="btn btn--ghost btn--sm" type="button" data-generate-coupon-code aria-label="تولید کد تصادفی">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 2v6h-6"></path>
                                    <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                                    <path d="M3 22v-6h6"></path>
                                    <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                                </svg>
                            </button>
                        </div>
                        <span class="field__hint">۵ تا ۸ کاراکتر، فقط حروف انگلیسی و عدد.</span>
                        @error('code')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">نوع</span>
                            @php($typeValue = old('discount_type', (string) ($coupon->discount_type ?? 'percent')))
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
                                value="{{ old('discount_value', (string) ($coupon->discount_value ?? 0)) }}">
                            @error('discount_value')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">شروع</span>
                            <input name="starts_at" data-jdp value="{{ old('starts_at', $coupon?->starts_at ? jdate($coupon->starts_at)->format('Y/m/d H:i') : '') }}">
                            @error('starts_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">پایان</span>
                            <input name="ends_at" data-jdp value="{{ old('ends_at', $coupon?->ends_at ? jdate($coupon->ends_at)->format('Y/m/d H:i') : '') }}">
                            @error('ends_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">سقف استفاده</span>
                            <input type="number" name="usage_limit" min="1" max="2000000000"
                                value="{{ old('usage_limit', (string) ($coupon->usage_limit ?? '')) }}">
                            @error('usage_limit')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <label class="field">
                        <span class="field__label">وضعیت</span>
                        <label class="cluster">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $coupon?->is_active ? '1' : '') === '1')>
                            <span>فعال</span>
                        </label>
                        @error('is_active')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">شرایط استفاده</span>
                        <label class="cluster">
                            <input type="checkbox" name="first_purchase_only" value="1" @checked(old('first_purchase_only', (($coupon->meta ?? [])['first_purchase_only'] ?? false) ? '1' : '0') === '1')>
                            <span>فقط خرید اول</span>
                        </label>
                        <span class="field__hint">هر کد فقط یک بار برای هر کاربر قابل استفاده است.</span>
                        @error('first_purchase_only')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="section__title section__title--sm">محدودیت محصولات</div>

                    <label class="field">
                        <span class="field__label">اعمال روی</span>
                        <label class="cluster">
                            <input type="checkbox" name="apply_all_products" value="1" @checked($applyAll) data-coupon-all-products>
                            <span>همه محصولات</span>
                        </label>
                        @error('apply_all_products')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">محصولات مجاز</span>
                        <select name="product_ids[]" multiple size="16" style="min-height: 320px;" @disabled($applyAll) data-coupon-products>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(in_array((int) $product->id, $oldSelected, true))>
                                    #{{ $product->id }} — {{ $product->title ?? 'محصول' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="field__hint">اگر همه محصولات غیرفعال باشد، حداقل یک محصول انتخاب کنید.</span>
                        @error('product_ids')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                        @error('product_ids.*')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="coupon-form">ذخیره</button>
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="coupon-delete-form">حذف کد تخفیف</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.coupons.destroy', $coupon->id) }}"
                        id="coupon-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف کد تخفیف"
                        data-confirm-message="آیا از حذف این کد تخفیف مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
