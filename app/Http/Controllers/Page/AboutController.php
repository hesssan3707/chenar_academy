<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $about = [
            'title' => 'درباره چنار آکادمی',
            'subtitle' => 'آموزش هدفمند برای دانشگاه، مدرسه و مسیر حرفه‌ای',
            'body' => "چنار آکادمی یک پلتفرم آموزشی برای ارائه‌ی جزوه‌ها و ویدیوهای کاربردی است.\n\nهدف ما ساده است: یادگیری را سریع‌تر، قابل فهم‌تر و در دسترس‌تر کنیم.",
        ];

        if (Schema::hasTable('settings')) {
            $setting = Setting::query()->where('key', 'page.about')->first();

            if ($setting && is_array($setting->value)) {
                $value = $setting->value;

                foreach (['title', 'subtitle', 'body'] as $key) {
                    if (isset($value[$key]) && is_string($value[$key]) && $value[$key] !== '') {
                        $about[$key] = $value[$key];
                    }
                }
            }
        }

        return view('pages.about', [
            'about' => $about,
        ]);
    }
}
