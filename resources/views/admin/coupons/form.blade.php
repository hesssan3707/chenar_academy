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

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.coupons.update', $coupon->id) : route('admin.coupons.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">کد</span>
                        <input name="code" required value="{{ old('code', (string) ($coupon->code ?? '')) }}">
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
                            <input name="starts_at" value="{{ old('starts_at', $coupon?->starts_at ? $coupon->starts_at->format('Y-m-d H:i') : '') }}">
                            @error('starts_at')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">پایان</span>
                            <input name="ends_at" value="{{ old('ends_at', $coupon?->ends_at ? $coupon->ends_at->format('Y-m-d H:i') : '') }}">
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

                        <label class="field">
                            <span class="field__label">سقف هر کاربر</span>
                            <input type="number" name="per_user_limit" min="1" max="2000000000"
                                value="{{ old('per_user_limit', (string) ($coupon->per_user_limit ?? '')) }}">
                            @error('per_user_limit')
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

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.coupons.destroy', $coupon->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف کد تخفیف</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
