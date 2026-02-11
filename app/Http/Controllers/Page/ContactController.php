<?php

namespace App\Http\Controllers\Page;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        $settingsLinks = collect([
            (object) [
                'platform' => 'instagram',
                'title' => 'اینستاگرام',
                'url' => $this->settingString('social.instagram.url'),
            ],
            (object) [
                'platform' => 'telegram',
                'title' => 'تلگرام',
                'url' => $this->settingString('social.telegram.url'),
            ],
            (object) [
                'platform' => 'youtube',
                'title' => 'یوتیوب',
                'url' => $this->settingString('social.youtube.url'),
            ],
        ])->filter(fn ($link) => trim((string) ($link->url ?? '')) !== '');

        $socialLinks = $settingsLinks;

        if ($socialLinks->isEmpty() && Schema::hasTable('social_links')) {
            $socialLinks = SocialLink::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
        }

        return view('pages.contact', [
            'socialLinks' => $socialLinks,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        abort(501);
    }
}
