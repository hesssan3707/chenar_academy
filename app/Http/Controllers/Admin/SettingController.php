<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        if (Schema::hasTable('settings')) {
            $setting = Setting::query()->where('key', 'page.about')->first();

            if ($setting && is_array($setting->value)) {
                $value = $setting->value;
                $about['title'] = is_string($value['title'] ?? null) ? (string) $value['title'] : '';
                $about['subtitle'] = is_string($value['subtitle'] ?? null) ? (string) $value['subtitle'] : '';
                $about['body'] = is_string($value['body'] ?? null) ? (string) $value['body'] : '';
            }
        }

        return view('admin.settings.index', [
            'title' => 'تنظیمات',
            'activeTheme' => app('theme')->active(),
            'themes' => app('theme')->available(),
            'about' => $about,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $themes = app('theme')->available();

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($themes)],
            'about_title' => ['nullable', 'string', 'max:120'],
            'about_subtitle' => ['nullable', 'string', 'max:255'],
            'about_body' => ['nullable', 'string', 'max:10000'],
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
        }

        Cache::forget('theme.active');

        return redirect()->route('admin.settings.index');
    }
}
