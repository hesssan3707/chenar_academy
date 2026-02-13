<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        $about = [
            'title' => '',
            'subtitle' => '',
            'body' => '',
        ];

        $reviewsArePublic = true;
        $ratingsArePublic = true;
        $reviewsRequireApproval = false;
        $accessExpirationDays = null;
        $currency = 'IRR';
        $taxPercent = 0;
        $cardToCardCard1Name = '';
        $cardToCardCard1Number = '';
        $cardToCardCard2Name = '';
        $cardToCardCard2Number = '';
        $socialInstagramUrl = '';
        $socialTelegramUrl = '';
        $socialYoutubeUrl = '';
        $logoMediaId = null;
        $backgroundDefaultMediaId = null;
        $backgroundHomeMediaId = null;
        $backgroundVideosMediaId = null;
        $backgroundBookletsMediaId = null;
        $backgroundOtherMediaId = null;
        $logoPreviewUrl = null;
        $backgroundDefaultPreviewUrl = null;
        $backgroundHomePreviewUrl = null;
        $backgroundVideosPreviewUrl = null;
        $backgroundBookletsPreviewUrl = null;
        $backgroundOtherPreviewUrl = null;

        if (Schema::hasTable('settings')) {
            $setting = Setting::query()->where('key', 'page.about')->first();

            if ($setting && is_array($setting->value)) {
                $value = $setting->value;
                $about['title'] = is_string($value['title'] ?? null) ? (string) $value['title'] : '';
                $about['subtitle'] = is_string($value['subtitle'] ?? null) ? (string) $value['subtitle'] : '';
                $about['body'] = is_string($value['body'] ?? null) ? (string) $value['body'] : '';
            }

            $reviewsArePublic = $this->settingBool('commerce.reviews.public', true);
            $ratingsArePublic = $this->settingBool('commerce.ratings.public', true);
            $reviewsRequireApproval = $this->settingBool('commerce.reviews.require_approval', false);
            $accessExpirationDays = $this->settingInt('commerce.access_expiration_days');
            $currency = $this->commerceCurrency();
            $taxPercent = $this->settingIntAllowZero('commerce.tax_percent', 0);
            $cardToCardCard1Name = $this->settingString('commerce.card_to_card.card1.name');
            $cardToCardCard1Number = $this->settingString('commerce.card_to_card.card1.number');
            $cardToCardCard2Name = $this->settingString('commerce.card_to_card.card2.name');
            $cardToCardCard2Number = $this->settingString('commerce.card_to_card.card2.number');
            $socialInstagramUrl = $this->settingString('social.instagram.url');
            $socialTelegramUrl = $this->settingString('social.telegram.url');
            $socialYoutubeUrl = $this->settingString('social.youtube.url');
            $logoMediaId = $this->settingInt('ui.logo_media_id');

            $backgroundSetting = Setting::query()->where('key', 'ui.backgrounds')->first();
            $backgroundValue = $backgroundSetting?->value;
            if (is_array($backgroundValue)) {
                $backgroundDefaultMediaId = isset($backgroundValue['default_media_id']) && is_numeric($backgroundValue['default_media_id']) ? (int) $backgroundValue['default_media_id'] : null;
                $backgroundHomeMediaId = isset($backgroundValue['home_media_id']) && is_numeric($backgroundValue['home_media_id']) ? (int) $backgroundValue['home_media_id'] : null;
                $backgroundVideosMediaId = isset($backgroundValue['videos_media_id']) && is_numeric($backgroundValue['videos_media_id']) ? (int) $backgroundValue['videos_media_id'] : null;
                $backgroundBookletsMediaId = isset($backgroundValue['booklets_media_id']) && is_numeric($backgroundValue['booklets_media_id']) ? (int) $backgroundValue['booklets_media_id'] : null;
                $backgroundOtherMediaId = isset($backgroundValue['other_media_id']) && is_numeric($backgroundValue['other_media_id']) ? (int) $backgroundValue['other_media_id'] : null;
            }

            if (Schema::hasTable('media')) {
                $mediaIds = collect([
                    $logoMediaId,
                    $backgroundDefaultMediaId,
                    $backgroundHomeMediaId,
                    $backgroundVideosMediaId,
                    $backgroundBookletsMediaId,
                    $backgroundOtherMediaId,
                ])->filter(fn ($id) => is_int($id) && $id > 0)->values();

                $mediaById = $mediaIds->isNotEmpty()
                    ? Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id')
                    : collect();

                $previewUrlFor = function (?int $mediaId) use ($mediaById): ?string {
                    if (! $mediaId || $mediaId <= 0) {
                        return null;
                    }

                    $media = $mediaById->get($mediaId);
                    if (! $media) {
                        return null;
                    }

                    $disk = (string) ($media->disk ?? '');
                    $mime = strtolower((string) ($media->mime_type ?? ''));
                    $path = (string) ($media->path ?? '');
                    if ($disk !== 'public' || $path === '' || ! str_starts_with($mime, 'image/')) {
                        return null;
                    }

                    return route('media.stream', $media->id);
                };

                $logoPreviewUrl = $previewUrlFor($logoMediaId);
                $backgroundDefaultPreviewUrl = $previewUrlFor($backgroundDefaultMediaId);
                $backgroundHomePreviewUrl = $previewUrlFor($backgroundHomeMediaId);
                $backgroundVideosPreviewUrl = $previewUrlFor($backgroundVideosMediaId);
                $backgroundBookletsPreviewUrl = $previewUrlFor($backgroundBookletsMediaId);
                $backgroundOtherPreviewUrl = $previewUrlFor($backgroundOtherMediaId);
            }
        }

        return view('admin.settings.index', [
            'title' => 'تنظیمات',
            'activeTheme' => app('theme')->active(),
            'themes' => app('theme')->available(),
            'about' => $about,
            'reviewsArePublic' => $reviewsArePublic,
            'ratingsArePublic' => $ratingsArePublic,
            'reviewsRequireApproval' => $reviewsRequireApproval,
            'accessExpirationDays' => $accessExpirationDays,
            'currency' => $currency,
            'taxPercent' => $taxPercent,
            'cardToCardCard1Name' => $cardToCardCard1Name,
            'cardToCardCard1Number' => $cardToCardCard1Number,
            'cardToCardCard2Name' => $cardToCardCard2Name,
            'cardToCardCard2Number' => $cardToCardCard2Number,
            'socialInstagramUrl' => $socialInstagramUrl,
            'socialTelegramUrl' => $socialTelegramUrl,
            'socialYoutubeUrl' => $socialYoutubeUrl,
            'logoMediaId' => $logoMediaId,
            'backgroundDefaultMediaId' => $backgroundDefaultMediaId,
            'backgroundHomeMediaId' => $backgroundHomeMediaId,
            'backgroundVideosMediaId' => $backgroundVideosMediaId,
            'backgroundBookletsMediaId' => $backgroundBookletsMediaId,
            'backgroundOtherMediaId' => $backgroundOtherMediaId,
            'logoPreviewUrl' => $logoPreviewUrl,
            'backgroundDefaultPreviewUrl' => $backgroundDefaultPreviewUrl,
            'backgroundHomePreviewUrl' => $backgroundHomePreviewUrl,
            'backgroundVideosPreviewUrl' => $backgroundVideosPreviewUrl,
            'backgroundBookletsPreviewUrl' => $backgroundBookletsPreviewUrl,
            'backgroundOtherPreviewUrl' => $backgroundOtherPreviewUrl,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $themes = app('theme')->available();

        $backgroundRule = ['nullable', 'integer'];
        if (Schema::hasTable('media')) {
            $backgroundRule[] = Rule::exists('media', 'id')
                ->where(fn ($query) => $query->where('disk', 'public')->where('mime_type', 'like', 'image/%'));
        }

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($themes)],
            'about_title' => ['nullable', 'string', 'max:120'],
            'about_subtitle' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string', 'max:10000'],
            'reviews_public' => ['nullable', 'boolean'],
            'ratings_public' => ['nullable', 'boolean'],
            'reviews_require_approval' => ['nullable', 'boolean'],
            'access_expiration_days' => ['nullable', 'integer', 'min:0', 'max:36500'],
            'currency' => ['nullable', 'string', 'size:3'],
            'tax_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'card_to_card_card1_name' => ['nullable', 'string', 'max:120'],
            'card_to_card_card1_number' => ['nullable', 'string', 'max:50'],
            'card_to_card_card2_name' => ['nullable', 'string', 'max:120'],
            'card_to_card_card2_number' => ['nullable', 'string', 'max:50'],
            'social_instagram_url' => ['nullable', 'string', 'max:255'],
            'social_telegram_url' => ['nullable', 'string', 'max:255'],
            'social_youtube_url' => ['nullable', 'string', 'max:255'],
            'logo_media_id' => $backgroundRule,
            'logo_file' => ['nullable', 'file', 'image', 'max:10240'],
            'background_default_media_id' => $backgroundRule,
            'background_home_media_id' => $backgroundRule,
            'background_videos_media_id' => $backgroundRule,
            'background_booklets_media_id' => $backgroundRule,
            'background_other_media_id' => $backgroundRule,
            'background_default_file' => ['nullable', 'file', 'image', 'max:10240'],
            'background_home_file' => ['nullable', 'file', 'image', 'max:10240'],
            'background_videos_file' => ['nullable', 'file', 'image', 'max:10240'],
            'background_booklets_file' => ['nullable', 'file', 'image', 'max:10240'],
            'background_other_file' => ['nullable', 'file', 'image', 'max:10240'],
        ]);

        if (Schema::hasTable('settings')) {
            Setting::query()->updateOrCreate(
                ['key' => config('theme.setting_key'), 'group' => 'theme'],
                ['value' => $validated['theme']]
            );

            $title = $validated['about_title'] ?? null;
            $subtitle = $validated['about_subtitle'] ?? null;
            $body = $validated['about_body'] ?? null;

            Setting::query()->updateOrCreate(
                ['key' => 'page.about', 'group' => 'pages'],
                ['value' => [
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'body' => $body,
                ]]
            );

            Setting::query()->updateOrCreate(
                ['key' => 'commerce.reviews.public', 'group' => 'commerce'],
                ['value' => $request->boolean('reviews_public')]
            );

            Setting::query()->updateOrCreate(
                ['key' => 'commerce.ratings.public', 'group' => 'commerce'],
                ['value' => $request->boolean('ratings_public')]
            );

            Setting::query()->updateOrCreate(
                ['key' => 'commerce.reviews.require_approval', 'group' => 'commerce'],
                ['value' => $request->boolean('reviews_require_approval')]
            );

            $days = (int) ($validated['access_expiration_days'] ?? 0);
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.access_expiration_days', 'group' => 'commerce'],
                ['value' => $days > 0 ? $days : null]
            );

            $currency = strtoupper(trim((string) ($validated['currency'] ?? 'IRR')));
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.currency', 'group' => 'commerce'],
                ['value' => strlen($currency) === 3 ? $currency : 'IRR']
            );

            $taxPercent = (int) ($validated['tax_percent'] ?? 0);
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.tax_percent', 'group' => 'commerce'],
                ['value' => min(100, max(0, $taxPercent))]
            );

            $card1Name = isset($validated['card_to_card_card1_name']) && trim((string) $validated['card_to_card_card1_name']) !== '' ? trim((string) $validated['card_to_card_card1_name']) : null;
            $card1Number = $this->normalizeCardNumber((string) ($validated['card_to_card_card1_number'] ?? ''));
            $card2Name = isset($validated['card_to_card_card2_name']) && trim((string) $validated['card_to_card_card2_name']) !== '' ? trim((string) $validated['card_to_card_card2_name']) : null;
            $card2Number = $this->normalizeCardNumber((string) ($validated['card_to_card_card2_number'] ?? ''));

            Setting::query()->updateOrCreate(
                ['key' => 'commerce.card_to_card.card1.name', 'group' => 'commerce'],
                ['value' => $card1Name]
            );
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.card_to_card.card1.number', 'group' => 'commerce'],
                ['value' => $card1Number]
            );
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.card_to_card.card2.name', 'group' => 'commerce'],
                ['value' => $card2Name]
            );
            Setting::query()->updateOrCreate(
                ['key' => 'commerce.card_to_card.card2.number', 'group' => 'commerce'],
                ['value' => $card2Number]
            );

            $instagramUrl = $this->normalizeUrl((string) ($validated['social_instagram_url'] ?? ''));
            $telegramUrl = $this->normalizeUrl((string) ($validated['social_telegram_url'] ?? ''));
            $youtubeUrl = $this->normalizeUrl((string) ($validated['social_youtube_url'] ?? ''));

            Setting::query()->updateOrCreate(
                ['key' => 'social.instagram.url', 'group' => 'social'],
                ['value' => $instagramUrl]
            );
            Setting::query()->updateOrCreate(
                ['key' => 'social.telegram.url', 'group' => 'social'],
                ['value' => $telegramUrl]
            );
            Setting::query()->updateOrCreate(
                ['key' => 'social.youtube.url', 'group' => 'social'],
                ['value' => $youtubeUrl]
            );

            $logoMediaId = ($validated['logo_media_id'] ?? null) !== null ? (int) $validated['logo_media_id'] : null;
            $backgroundDefaultMediaId = ($validated['background_default_media_id'] ?? null) !== null ? (int) $validated['background_default_media_id'] : null;
            $backgroundHomeMediaId = ($validated['background_home_media_id'] ?? null) !== null ? (int) $validated['background_home_media_id'] : null;
            $backgroundVideosMediaId = ($validated['background_videos_media_id'] ?? null) !== null ? (int) $validated['background_videos_media_id'] : null;
            $backgroundBookletsMediaId = ($validated['background_booklets_media_id'] ?? null) !== null ? (int) $validated['background_booklets_media_id'] : null;
            $backgroundOtherMediaId = ($validated['background_other_media_id'] ?? null) !== null ? (int) $validated['background_other_media_id'] : null;

            if (Schema::hasTable('media')) {
                $logoFile = $request->file('logo_file');
                if ($logoFile instanceof UploadedFile) {
                    $logoMediaId = $this->storeUploadedMedia($logoFile, 'public', 'media')->id;
                }

                $defaultFile = $request->file('background_default_file');
                if ($defaultFile instanceof UploadedFile) {
                    $backgroundDefaultMediaId = $this->storeUploadedMedia($defaultFile, 'public', 'media')->id;
                }

                $homeFile = $request->file('background_home_file');
                if ($homeFile instanceof UploadedFile) {
                    $backgroundHomeMediaId = $this->storeUploadedMedia($homeFile, 'public', 'media')->id;
                }

                $videosFile = $request->file('background_videos_file');
                if ($videosFile instanceof UploadedFile) {
                    $backgroundVideosMediaId = $this->storeUploadedMedia($videosFile, 'public', 'media')->id;
                }

                $bookletsFile = $request->file('background_booklets_file');
                if ($bookletsFile instanceof UploadedFile) {
                    $backgroundBookletsMediaId = $this->storeUploadedMedia($bookletsFile, 'public', 'media')->id;
                }

                $otherFile = $request->file('background_other_file');
                if ($otherFile instanceof UploadedFile) {
                    $backgroundOtherMediaId = $this->storeUploadedMedia($otherFile, 'public', 'media')->id;
                }
            }

            Setting::query()->updateOrCreate(
                ['key' => 'ui.logo_media_id', 'group' => 'ui'],
                ['value' => $logoMediaId]
            );

            Setting::query()->updateOrCreate(
                ['key' => 'ui.backgrounds', 'group' => 'ui'],
                ['value' => [
                    'default_media_id' => $backgroundDefaultMediaId,
                    'home_media_id' => $backgroundHomeMediaId,
                    'videos_media_id' => $backgroundVideosMediaId,
                    'booklets_media_id' => $backgroundBookletsMediaId,
                    'other_media_id' => $backgroundOtherMediaId,
                ]]
            );
        }

        Cache::forget('theme.active');

        return redirect()->route('admin.settings.index');
    }

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): Media
    {
        $path = $file->store($directory, $disk);
        $path = str_replace('\\', '/', (string) $path);

        $width = null;
        $height = null;

        $mime = (string) ($file->getMimeType() ?: '');
        if ($mime !== '' && str_starts_with(strtolower($mime), 'image/')) {
            $imageSize = @getimagesize($file->getPathname());
            if (is_array($imageSize) && isset($imageSize[0], $imageSize[1])) {
                $width = is_numeric($imageSize[0]) ? (int) $imageSize[0] : null;
                $height = is_numeric($imageSize[1]) ? (int) $imageSize[1] : null;
            }
        }

        $sha1 = null;
        try {
            $sha1 = sha1_file($file->getPathname()) ?: null;
        } catch (\Throwable) {
            $sha1 = null;
        }

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => $sha1,
            'width' => $width,
            'height' => $height,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }

    private function normalizeCardNumber(string $raw): ?string
    {
        $normalized = trim($raw);
        if ($normalized === '') {
            return null;
        }

        $normalized = strtr($normalized, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        $digits = preg_replace('/\\D+/', '', $normalized);
        $digits = is_string($digits) ? $digits : '';

        return $digits !== '' ? $digits : null;
    }

    private function normalizeUrl(string $raw): ?string
    {
        $normalized = trim($raw);
        if ($normalized === '') {
            return null;
        }

        if (preg_match('/^https?:\\/\\//i', $normalized) === 1) {
            return $normalized;
        }

        return 'https://'.$normalized;
    }

    private function settingBool(string $key, bool $default): bool
    {
        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        if (is_array($value)) {
            if (array_key_exists('enabled', $value) && is_bool($value['enabled'])) {
                return $value['enabled'];
            }

            if (array_key_exists('value', $value)) {
                $value = $value['value'];
            } elseif (count($value) === 1) {
                $value = reset($value);
            }
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }

    private function settingInt(string $key): ?int
    {
        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return null;
        }

        $value = $setting->value;
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            $intValue = (int) $value;

            return $intValue > 0 ? $intValue : null;
        }

        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return null;
            }

            if (is_numeric($normalized)) {
                $intValue = (int) $normalized;

                return $intValue > 0 ? $intValue : null;
            }
        }

        return null;
    }

    private function settingIntAllowZero(string $key, int $default): int
    {
        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;
        if ($value === null) {
            return $default;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return $default;
            }

            if (is_numeric($normalized)) {
                return (int) $normalized;
            }
        }

        return $default;
    }
}
