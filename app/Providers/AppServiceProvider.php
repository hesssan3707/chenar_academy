<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductAccess;
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

        View::composer('layouts.app', function ($view) {
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
    }
}
