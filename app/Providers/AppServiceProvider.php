<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\SocialLink;
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
    }
}
