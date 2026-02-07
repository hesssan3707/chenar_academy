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
        $socialLinks = collect();
        if (Schema::hasTable('social_links')) {
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
