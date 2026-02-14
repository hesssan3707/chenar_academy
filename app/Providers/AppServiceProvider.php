<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductAccess;
use App\Models\Setting;
use App\Models\SocialLink;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $commerceCurrency = 'IRR';
        if (Schema::hasTable('settings')) {
            $raw = Setting::query()->where('key', 'commerce.currency')->value('value');
            $raw = is_string($raw) ? strtoupper(trim($raw)) : '';
            if (in_array($raw, ['IRR', 'IRT'], true)) {
                $commerceCurrency = $raw;
            }
        }

        View::share('commerceCurrency', $commerceCurrency);
        View::share('commerceCurrencyLabel', $commerceCurrency === 'IRT' ? 'تومان' : 'ریال');

        $clearCacheGroup = function (string $group): void {
            $keyListKey = "content_cache_keys.{$group}";

            $keys = Cache::pull($keyListKey, []);
            if (! is_array($keys)) {
                $keys = [];
            }

            foreach ($keys as $cacheKey) {
                if (is_string($cacheKey) && $cacheKey !== '') {
                    Cache::forget($cacheKey);
                }
            }
        };

        Product::saved(function () use ($clearCacheGroup) {
            $clearCacheGroup('products');
            $clearCacheGroup('home');
        });

        Product::deleted(function () use ($clearCacheGroup) {
            $clearCacheGroup('products');
            $clearCacheGroup('home');
        });

        Category::saved(function () use ($clearCacheGroup) {
            $clearCacheGroup('products');
        });

        Category::deleted(function () use ($clearCacheGroup) {
            $clearCacheGroup('products');
        });

        Post::saved(function () use ($clearCacheGroup) {
            $clearCacheGroup('posts');
            $clearCacheGroup('home');
        });

        Post::deleted(function () use ($clearCacheGroup) {
            $clearCacheGroup('posts');
            $clearCacheGroup('home');
        });

        View::composer('partials.header', function ($view) {
            $socialLinks = collect();
            if (Schema::hasTable('social_links')) {
                $socialLinks = SocialLink::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();
            }

            $view->with('socialLinks', $socialLinks);

            if (! Schema::hasTable('carts') || ! Schema::hasTable('cart_items')) {
                $view->with('cartItemCount', 0);

                return;
            }

            $request = request();

            $cartId = null;

            if (auth()->check()) {
                $cartId = Cart::query()
                    ->where('user_id', auth()->id())
                    ->where('status', 'active')
                    ->latest('id')
                    ->value('id');
            } else {
                $token = $request->cookie('cart_token')
                    ?: $request->session()->get('cart_token')
                    ?: $request->session()->getId();

                if (is_string($token) && $token !== '') {
                    $cartId = Cart::query()
                        ->where('session_id', $token)
                        ->where('status', 'active')
                        ->latest('id')
                        ->value('id');
                }
            }

            $count = 0;
            if ($cartId) {
                $count = CartItem::query()->where('cart_id', $cartId)->count();
            }

            $view->with('cartItemCount', $count);
        });

        View::composer('partials.footer', function ($view) {
            $socialLinks = collect();
            if (Schema::hasTable('social_links')) {
                $socialLinks = SocialLink::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get();
            }

            $view->with('socialLinks', $socialLinks);
        });

        View::composer(['layouts.app', 'layouts.spa'], function ($view) {
            if (! Schema::hasTable('surveys') || ! Schema::hasTable('survey_responses')) {
                return;
            }

            $now = now();

            $survey = Survey::query()
                ->where('is_active', true)
                ->where(function ($query) use ($now) {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($query) use ($now) {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                })
                ->orderByDesc('id')
                ->first();

            if (! $survey) {
                return;
            }

            $audience = (string) $survey->audience;
            $user = auth()->user();

            if ($audience === 'authenticated' && ! $user) {
                return;
            }

            if ($audience === 'purchasers') {
                if (! $user) {
                    return;
                }

                $isPurchaser = ProductAccess::query()->where('user_id', $user->id)->exists();
                if (! $isPurchaser) {
                    return;
                }
            }

            $hasResponse = false;

            if ($user) {
                $hasResponse = SurveyResponse::query()
                    ->where('survey_id', $survey->id)
                    ->where('user_id', $user->id)
                    ->exists();
            } else {
                $token = request()->cookie('survey_anon_token');
                if (is_string($token) && $token !== '') {
                    try {
                        $decrypted = app('encrypter')->decrypt($token, false);
                        if (is_string($decrypted) && $decrypted !== '') {
                            $token = \Illuminate\Cookie\CookieValuePrefix::remove($decrypted, 'survey_anon_token');
                        }
                    } catch (\Throwable) {
                    }

                    $hasResponse = SurveyResponse::query()
                        ->where('survey_id', $survey->id)
                        ->where('anon_token', $token)
                        ->exists();
                }
            }

            if ($hasResponse) {
                return;
            }

            $view->with('activeSurvey', $survey);
        });

        View::composer('layouts.spa', function ($view) {
            $backgrounds = [
                'home' => '',
                'videos' => '',
                'booklets' => '',
                'other' => '',
            ];
            $logoUrl = '';

            if (! Schema::hasTable('settings')) {
                $view->with('spaBackgrounds', $backgrounds);
                $view->with('spaLogoUrl', $logoUrl);

                return;
            }

            $logoSetting = Setting::query()->where('key', 'ui.logo_media_id')->first();
            $logoId = $logoSetting?->value;
            $logoMediaId = is_numeric($logoId) ? (int) $logoId : null;

            $setting = Setting::query()->where('key', 'ui.backgrounds')->first();
            $value = $setting?->value;

            if (! is_array($value)) {
                $view->with('spaBackgrounds', $backgrounds);
                if ($logoMediaId && $logoMediaId > 0 && Schema::hasTable('media')) {
                    $logoMedia = Media::query()->where('id', $logoMediaId)->first();
                    if ($logoMedia) {
                        $disk = (string) ($logoMedia->disk ?? '');
                        $mime = strtolower((string) ($logoMedia->mime_type ?? ''));
                        $path = (string) ($logoMedia->path ?? '');
                        if ($disk === 'public' && $path !== '' && str_starts_with($mime, 'image/')) {
                            $logoUrl = route('media.stream', $logoMedia->id);
                        }
                    }
                }
                $view->with('spaLogoUrl', $logoUrl);

                return;
            }

            $map = [
                'default' => isset($value['default_media_id']) && is_numeric($value['default_media_id']) ? (int) $value['default_media_id'] : null,
                'home' => isset($value['home_media_id']) && is_numeric($value['home_media_id']) ? (int) $value['home_media_id'] : null,
                'videos' => isset($value['videos_media_id']) && is_numeric($value['videos_media_id']) ? (int) $value['videos_media_id'] : null,
                'booklets' => isset($value['booklets_media_id']) && is_numeric($value['booklets_media_id']) ? (int) $value['booklets_media_id'] : null,
                'other' => isset($value['other_media_id']) && is_numeric($value['other_media_id']) ? (int) $value['other_media_id'] : null,
            ];

            $ids = collect($map)->filter(fn ($id) => is_int($id) && $id > 0)->unique()->values();
            if ($logoMediaId && $logoMediaId > 0) {
                $ids = $ids->push($logoMediaId)->unique()->values();
            }

            if ($ids->isEmpty() || ! Schema::hasTable('media')) {
                $view->with('spaBackgrounds', $backgrounds);
                $view->with('spaLogoUrl', $logoUrl);

                return;
            }

            $mediaById = Media::query()->whereIn('id', $ids)->get()->keyBy('id');

            if ($logoMediaId && $logoMediaId > 0) {
                $logoMedia = $mediaById->get($logoMediaId);
                if ($logoMedia) {
                    $disk = (string) ($logoMedia->disk ?? '');
                    $mime = strtolower((string) ($logoMedia->mime_type ?? ''));
                    $path = (string) ($logoMedia->path ?? '');
                    if ($disk === 'public' && $path !== '' && str_starts_with($mime, 'image/')) {
                        $logoUrl = route('media.stream', $logoMedia->id);
                    }
                }
            }

            foreach ($map as $group => $mediaId) {
                if (! $mediaId || $mediaId <= 0) {
                    continue;
                }

                $media = $mediaById->get($mediaId);
                if (! $media) {
                    continue;
                }

                $disk = (string) ($media->disk ?? '');
                $mime = strtolower((string) ($media->mime_type ?? ''));
                $path = (string) ($media->path ?? '');
                if ($disk !== 'public' || $path === '' || ! str_starts_with($mime, 'image/')) {
                    continue;
                }

                if ($group === 'default') {
                    $backgrounds['home'] = $backgrounds['home'] ?: route('media.stream', $media->id);
                    $backgrounds['videos'] = $backgrounds['videos'] ?: route('media.stream', $media->id);
                    $backgrounds['booklets'] = $backgrounds['booklets'] ?: route('media.stream', $media->id);
                    $backgrounds['other'] = $backgrounds['other'] ?: route('media.stream', $media->id);

                    continue;
                }

                $backgrounds[$group] = route('media.stream', $media->id);
            }

            $view->with('spaBackgrounds', $backgrounds);
            $view->with('spaLogoUrl', $logoUrl);
        });
    }
}
