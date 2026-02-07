<?php

namespace App\Theme;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ThemeManager
{
    public function active(): string
    {
        $default = (string) config('theme.default', 'default');
        $settingKey = (string) config('theme.setting_key', 'theme.active');

        $active = Cache::remember('theme.active', 60, function () use ($default, $settingKey) {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            $setting = Setting::query()->where('key', $settingKey)->first();

            if (! $setting) {
                return $default;
            }

            $value = $setting->value;

            if (is_string($value) && $value !== '') {
                return $value;
            }

            return $default;
        });

        if (! $this->isAvailable($active)) {
            return $default;
        }

        return $active;
    }

    public function available(): array
    {
        $available = config('theme.available', ['default']);

        if (! is_array($available)) {
            return ['default'];
        }

        return array_values(array_filter($available, fn ($value) => is_string($value) && $value !== ''));
    }

    public function isAvailable(string $theme): bool
    {
        return in_array($theme, $this->available(), true);
    }
}
