<?php

namespace App\Providers;

use App\Theme\ThemeManager;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ThemeManager::class, fn () => new ThemeManager);
        $this->app->alias(ThemeManager::class, 'theme');
    }
}
