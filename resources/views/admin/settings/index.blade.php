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

            <form method="post" action="{{ route('admin.settings.update') }}" class="stack stack--sm">
                @csrf
                @method('put')

                <div class="settings-tabs settings-tabs--floating" data-settings-tabs>
                    <div class="settings-tabs__bar" role="tablist" aria-label="Settings Sections">
                        <button class="settings-tab is-active" type="button" role="tab" aria-selected="true"
                            data-settings-tab="general">عمومی</button>
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="about">درباره ما</button>
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="reviews">نظرات</button>
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="access">انقضا</button>
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="card">کارت‌به‌کارت</button>
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="social">شبکه‌های اجتماعی</button>
                    </div>
                </div>

                <div class="panel">
                    <div class="settings-tab-panel" data-settings-panel="general">
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
                    </div>

                    <div class="settings-tab-panel" data-settings-panel="about" hidden>
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
                    </div>

                    <div class="settings-tab-panel" data-settings-panel="reviews" hidden>
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

                        <label class="field">
                            <span class="field__label">تایید ادمین برای نمایش نظر</span>
                            @php($approvalValue = (string) old('reviews_require_approval', ($reviewsRequireApproval ?? false) ? '1' : '0'))
                            <select name="reviews_require_approval">
                                <option value="1" @selected($approvalValue === '1')>نیاز به تایید</option>
                                <option value="0" @selected($approvalValue === '0')>بدون تایید</option>
                            </select>
                            @error('reviews_require_approval')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="settings-tab-panel" data-settings-panel="access" hidden>
                        <label class="field">
                            <span class="field__label">مدت اعتبار دسترسی (روز)</span>
                            <input type="number" name="access_expiration_days" min="0" max="36500" value="{{ old('access_expiration_days', (string) ($accessExpirationDays ?? '')) }}" placeholder="بدون انقضا">
                            <div class="field__hint">برای فعال‌سازی، تعداد روز را وارد کنید. مقدار ۰ یا خالی یعنی بدون انقضا.</div>
                            @error('access_expiration_days')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="settings-tab-panel" data-settings-panel="card" hidden>
                        <div class="grid admin-grid-2 admin-grid-2--flush">
                            <label class="field">
                                <span class="field__label">نام صاحب کارت ۱</span>
                                <input name="card_to_card_card1_name" value="{{ old('card_to_card_card1_name', (string) ($cardToCardCard1Name ?? '')) }}" placeholder="مثلاً: چنار آکادمی">
                                @error('card_to_card_card1_name')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">شماره کارت ۱</span>
                                <input name="card_to_card_card1_number" value="{{ old('card_to_card_card1_number', (string) ($cardToCardCard1Number ?? '')) }}" placeholder="مثلاً: 6037-9918-XXXX-XXXX">
                                <div class="field__hint">فقط اعداد ذخیره می‌شود؛ فاصله و خط تیره مشکلی ندارد.</div>
                                @error('card_to_card_card1_number')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">نام صاحب کارت ۲</span>
                                <input name="card_to_card_card2_name" value="{{ old('card_to_card_card2_name', (string) ($cardToCardCard2Name ?? '')) }}" placeholder="اختیاری">
                                @error('card_to_card_card2_name')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">شماره کارت ۲</span>
                                <input name="card_to_card_card2_number" value="{{ old('card_to_card_card2_number', (string) ($cardToCardCard2Number ?? '')) }}" placeholder="اختیاری">
                                <div class="field__hint">فقط اعداد ذخیره می‌شود؛ فاصله و خط تیره مشکلی ندارد.</div>
                                @error('card_to_card_card2_number')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>
                        </div>
                    </div>

                    <div class="settings-tab-panel" data-settings-panel="social" hidden>
                        <div class="grid admin-grid-2 admin-grid-2--flush">
                            <label class="field">
                                <span class="field__label">اینستاگرام</span>
                                <input name="social_instagram_url" dir="ltr" value="{{ old('social_instagram_url', (string) ($socialInstagramUrl ?? '')) }}" placeholder="instagram.com/chenar_academy">
                                @error('social_instagram_url')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">تلگرام</span>
                                <input name="social_telegram_url" dir="ltr" value="{{ old('social_telegram_url', (string) ($socialTelegramUrl ?? '')) }}" placeholder="t.me/chenar_academy">
                                @error('social_telegram_url')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">یوتیوب</span>
                                <input name="social_youtube_url" dir="ltr" value="{{ old('social_youtube_url', (string) ($socialYoutubeUrl ?? '')) }}" placeholder="youtube.com/@chenaracademy">
                                @error('social_youtube_url')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        (function() {
            const root = document.querySelector('[data-settings-tabs]');
            if (!(root instanceof HTMLElement) || root.dataset.bound === '1') return;
            root.dataset.bound = '1';

            const tabs = Array.from(root.querySelectorAll('[data-settings-tab]'));
            const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
            if (tabs.length === 0 || panels.length === 0) return;

            const activate = (key) => {
                tabs.forEach((tab) => {
                    const tabKey = tab.getAttribute('data-settings-tab');
                    const isActive = tabKey === key;
                    tab.classList.toggle('is-active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    const panelKey = panel.getAttribute('data-settings-panel');
                    panel.hidden = panelKey !== key;
                });

                try {
                    window.localStorage.setItem('admin_settings_tab', String(key));
                } catch (e) {}
            };

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    const key = tab.getAttribute('data-settings-tab');
                    if (key) activate(key);
                });
            });

            const firstErrorPanel = panels.find((panel) => panel.querySelector('.field__error'));
            if (firstErrorPanel) {
                const key = firstErrorPanel.getAttribute('data-settings-panel');
                if (key) {
                    activate(key);
                    return;
                }
            }

            try {
                const saved = window.localStorage.getItem('admin_settings_tab');
                if (saved) {
                    const exists = tabs.some((t) => t.getAttribute('data-settings-tab') === saved);
                    if (exists) {
                        activate(saved);
                        return;
                    }
                }
            } catch (e) {}

            const firstKey = tabs[0] ? tabs[0].getAttribute('data-settings-tab') : null;
            if (firstKey) activate(firstKey);
        })();
    </script>
@endsection
