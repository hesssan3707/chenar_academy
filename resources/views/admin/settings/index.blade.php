@extends('layouts.admin')

@section('title', 'تنظیمات')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">تنظیمات</h1>
                    <p class="page-subtitle">تنظیمات پایه سایت و قالب بصری</p>
                </div>
            </div>

            <div class="panel">
                <form method="post" action="{{ route('admin.settings.update') }}" class="stack stack--sm">
                    @csrf
                    @method('put')

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">قالب (Theme)</span>
                            <select name="theme" required>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme }}" @selected($activeTheme === $theme)>{{ $theme }}</option>
                                @endforeach
                            </select>
                            @error('theme')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">واحد پول (کد ۳ حرفی)</span>
                            <input name="currency" value="{{ old('currency', (string) ($currency ?? 'IRR')) }}">
                            @error('currency')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">درصد مالیات</span>
                            <input type="number" name="tax_percent" min="0" max="100"
                                value="{{ old('tax_percent', (string) ($taxPercent ?? 0)) }}">
                            @error('tax_percent')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="divider"></div>

                    <div class="field__label">صفحه درباره ما</div>

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="about_title" value="{{ old('about_title', (string) ($about['title'] ?? '')) }}">
                        @error('about_title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">زیرعنوان</span>
                        <input name="about_subtitle" value="{{ old('about_subtitle', (string) ($about['subtitle'] ?? '')) }}">
                        @error('about_subtitle')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">متن</span>
                        <textarea name="about_body">{{ old('about_body', (string) ($about['body'] ?? '')) }}</textarea>
                        @error('about_body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="divider"></div>

                    <div class="field__label">نظرات و امتیازدهی</div>

                    <label class="field">
                        <span class="field__label">نمایش نظرات برای کاربران</span>
                        @php($reviewsPublicValue = (string) old('reviews_public', ($reviewsArePublic ?? true) ? '1' : '0'))
                        <select name="reviews_public">
                            <option value="1" @selected($reviewsPublicValue === '1')>نمایش عمومی</option>
                            <option value="0" @selected($reviewsPublicValue === '0')>فقط برای ادمین</option>
                        </select>
                        @error('reviews_public')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">نمایش امتیاز برای کاربران</span>
                        @php($ratingsPublicValue = (string) old('ratings_public', ($ratingsArePublic ?? true) ? '1' : '0'))
                        <select name="ratings_public">
                            <option value="1" @selected($ratingsPublicValue === '1')>نمایش عمومی</option>
                            <option value="0" @selected($ratingsPublicValue === '0')>فقط برای ادمین</option>
                        </select>
                        @error('ratings_public')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="divider"></div>

                    <div class="field__label">انقضای دسترسی پس از خرید</div>

                    <label class="field">
                        <span class="field__label">مدت اعتبار دسترسی (روز)</span>
                        <input type="number" name="access_expiration_days" min="0" max="36500" value="{{ old('access_expiration_days', (string) ($accessExpirationDays ?? '')) }}" placeholder="بدون انقضا">
                        <div class="field__hint">برای فعال‌سازی، تعداد روز را وارد کنید. مقدار ۰ یا خالی یعنی بدون انقضا.</div>
                        @error('access_expiration_days')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
