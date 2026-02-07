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
        return view('admin.settings.index', [
            'title' => 'تنظیمات',
            'activeTheme' => app('theme')->active(),
            'themes' => app('theme')->available(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $themes = app('theme')->available();

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($themes)],
        ]);

        if (Schema::hasTable('settings')) {
            Setting::query()->updateOrCreate(
                ['key' => config('theme.setting_key'), 'group' => 'theme'],
                ['value' => $validated['theme']]
            );
        }

        Cache::forget('theme.active');

        return redirect()->route('admin.settings.index');
    }
}
