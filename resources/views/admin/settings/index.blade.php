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

            <form method="post" action="{{ route('admin.settings.update') }}" class="stack stack--sm" enctype="multipart/form-data">
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
                        <button class="settings-tab" type="button" role="tab" aria-selected="false"
                            data-settings-tab="backgrounds">پس‌زمینه‌ها</button>
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
                                <span class="field__label">لوگوی سایت</span>
                                <input type="hidden" name="logo_media_id" value="{{ old('logo_media_id', (string) ($logoMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="logo_media_id">
                                    {{ old('logo_media_id', (string) ($logoMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="logo_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="logo_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="logo_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($logoPreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $logoPreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش لوگو">
                                            <img src="{{ $logoPreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('logo_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('logo_media_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">واحد پول</span>
                                @php($currencyValue = strtoupper((string) old('currency', (string) ($currency ?? 'IRR'))))
                                <select name="currency" required>
                                    <option value="IRR" @selected($currencyValue === 'IRR')>ریال</option>
                                    <option value="IRT" @selected($currencyValue === 'IRT')>تومان</option>
                                </select>
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

                    <div class="settings-tab-panel" data-settings-panel="backgrounds" hidden>
                        <div class="grid admin-grid-2 admin-grid-2--flush">
                            <label class="field">
                                <span class="field__label">پس‌زمینه پیش‌فرض</span>
                                <input type="hidden" name="background_default_media_id"
                                    value="{{ old('background_default_media_id', (string) ($backgroundDefaultMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="background_default_media_id">
                                    {{ old('background_default_media_id', (string) ($backgroundDefaultMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="background_default_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="background_default_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="background_default_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($backgroundDefaultPreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $backgroundDefaultPreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش پس‌زمینه پیش‌فرض">
                                            <img src="{{ $backgroundDefaultPreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('background_default_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('background_default_media_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">پس‌زمینه صفحه خانه</span>
                                <input type="hidden" name="background_home_media_id"
                                    value="{{ old('background_home_media_id', (string) ($backgroundHomeMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="background_home_media_id">
                                    {{ old('background_home_media_id', (string) ($backgroundHomeMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="background_home_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="background_home_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="background_home_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($backgroundHomePreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $backgroundHomePreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش پس‌زمینه صفحه خانه">
                                            <img src="{{ $backgroundHomePreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('background_home_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('background_home_media_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">پس‌زمینه صفحات ویدیو</span>
                                <input type="hidden" name="background_videos_media_id"
                                    value="{{ old('background_videos_media_id', (string) ($backgroundVideosMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="background_videos_media_id">
                                    {{ old('background_videos_media_id', (string) ($backgroundVideosMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="background_videos_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="background_videos_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="background_videos_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($backgroundVideosPreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $backgroundVideosPreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش پس‌زمینه صفحات ویدیو">
                                            <img src="{{ $backgroundVideosPreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('background_videos_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('background_videos_media_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">پس‌زمینه صفحات جزوه/پی‌دی‌اف</span>
                                <input type="hidden" name="background_booklets_media_id"
                                    value="{{ old('background_booklets_media_id', (string) ($backgroundBookletsMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="background_booklets_media_id">
                                    {{ old('background_booklets_media_id', (string) ($backgroundBookletsMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="background_booklets_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="background_booklets_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="background_booklets_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($backgroundBookletsPreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $backgroundBookletsPreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش پس‌زمینه صفحات جزوه">
                                            <img src="{{ $backgroundBookletsPreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('background_booklets_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('background_booklets_media_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">پس‌زمینه سایر صفحات</span>
                                <input type="hidden" name="background_other_media_id"
                                    value="{{ old('background_other_media_id', (string) ($backgroundOtherMediaId ?? '')) }}">
                                <div class="field__hint" data-bg-id-text="background_other_media_id">
                                    {{ old('background_other_media_id', (string) ($backgroundOtherMediaId ?? '')) ?: '—' }}
                                </div>
                                <input type="file" name="background_other_file" accept="image/*">
                                <div class="field__hint">
                                    <button class="btn btn--secondary btn--sm" type="button"
                                        data-open-media-picker="background_other_media_id">انتخاب از رسانه‌ها</button>
                                    <button class="btn btn--ghost btn--sm" type="button"
                                        data-clear-media-picker="background_other_media_id">پاک کردن</button>
                                </div>
                                @if (! empty($backgroundOtherPreviewUrl))
                                    <div class="field__hint">
                                        <button type="button"
                                            style="all: unset; cursor: zoom-in; display: inline-block;"
                                            data-media-preview-src="{{ $backgroundOtherPreviewUrl }}"
                                            data-media-preview-type="image"
                                            data-media-preview-label="پیش‌نمایش پس‌زمینه سایر صفحات">
                                            <img src="{{ $backgroundOtherPreviewUrl }}" alt="" style="max-width:260px;max-height:140px;border-radius:12px;border:1px solid rgba(255,255,255,.12); display: block;">
                                        </button>
                                    </div>
                                @endif
                                @error('background_other_file')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('background_other_media_id')
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

    <div class="admin-modal" data-media-picker-modal data-media-index-url="{{ route('admin.media.index') }}" hidden>
        <div class="admin-modal__backdrop" data-media-picker-close></div>
        <div class="admin-modal__panel">
            <div class="admin-modal__header">
                <div class="admin-modal__title">انتخاب از رسانه‌ها</div>
                <button class="btn btn--ghost btn--sm" type="button" data-media-picker-close>بستن</button>
            </div>
            <iframe class="admin-modal__frame" data-media-picker-frame title="Media Picker"></iframe>
        </div>
    </div>

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
            let initialKey = null;
            if (firstErrorPanel) {
                initialKey = firstErrorPanel.getAttribute('data-settings-panel');
            }

            if (!initialKey) {
                try {
                    const saved = window.localStorage.getItem('admin_settings_tab');
                    if (saved) {
                        const exists = tabs.some((t) => t.getAttribute('data-settings-tab') === saved);
                        if (exists) {
                            initialKey = saved;
                        }
                    }
                } catch (e) {}
            }

            if (!initialKey) {
                initialKey = tabs[0] ? tabs[0].getAttribute('data-settings-tab') : null;
            }

            if (initialKey) {
                activate(initialKey);
            }

            const modal = document.querySelector('[data-media-picker-modal]');
            const frame = modal ? modal.querySelector('[data-media-picker-frame]') : null;
            const mediaIndexUrl = modal instanceof HTMLElement ? (modal.getAttribute('data-media-index-url') || '') : '';

            const closeModal = () => {
                if (!modal) return;
                modal.hidden = true;
                document.body.classList.remove('has-modal');
                if (frame instanceof HTMLIFrameElement) {
                    frame.src = 'about:blank';
                }
            };

            const openModal = (fieldName) => {
                if (!modal || !(frame instanceof HTMLIFrameElement)) return;
                const url = mediaIndexUrl + '?picker=1&field=' + encodeURIComponent(String(fieldName));
                frame.src = url;
                modal.hidden = false;
                document.body.classList.add('has-modal');
            };

            const setFieldValue = (fieldName, value) => {
                const input = document.querySelector('input[name="' + String(fieldName) + '"]');
                if (!(input instanceof HTMLInputElement)) return;
                input.value = value ? String(value) : '';

                const text = document.querySelector('[data-bg-id-text="' + String(fieldName) + '"]');
                if (text) {
                    text.textContent = input.value ? input.value : '—';
                }
            };

            document.querySelectorAll('[data-open-media-picker]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const field = btn.getAttribute('data-open-media-picker');
                    if (field) openModal(field);
                });
            });

            document.querySelectorAll('[data-clear-media-picker]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const field = btn.getAttribute('data-clear-media-picker');
                    if (field) setFieldValue(field, '');
                });
            });

            if (modal) {
                modal.querySelectorAll('[data-media-picker-close]').forEach((btn) => {
                    btn.addEventListener('click', closeModal);
                });
            }

            window.addEventListener('message', (event) => {
                if (event.origin !== window.location.origin) return;
                const data = event.data;
                if (!data || data.type !== 'admin-media-picked') return;
                if (!data.field || !data.mediaId) return;
                setFieldValue(data.field, data.mediaId);
                closeModal();
            });
        })();
    </script>
@endsection
