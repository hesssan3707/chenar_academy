<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\CartItem;
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

        View::composer('partials.header', function ($view) {
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
    }
}
