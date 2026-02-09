<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        $socialLinks = SocialLink::query()->orderBy('sort_order')->orderBy('id')->paginate(40);

        return view('admin.social-links.index', [
            'title' => 'شبکه‌های اجتماعی',
            'socialLinks' => $socialLinks,
        ]);
    }

    public function create(): View
    {
        return view('admin.social-links.form', [
            'title' => 'ایجاد لینک شبکه اجتماعی',
            'socialLink' => new SocialLink([
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $socialLink = SocialLink::query()->create($validated + ['meta' => []]);

        return redirect()->route('admin.social-links.edit', $socialLink->id);
    }

    public function show(int $social_link): RedirectResponse
    {
        return redirect()->route('admin.social-links.edit', $social_link);
    }

    public function edit(int $social_link): View
    {
        $socialLink = SocialLink::query()->findOrFail($social_link);

        return view('admin.social-links.form', [
            'title' => 'ویرایش لینک شبکه اجتماعی',
            'socialLink' => $socialLink,
        ]);
    }

    public function update(Request $request, int $social_link): RedirectResponse
    {
        $socialLink = SocialLink::query()->findOrFail($social_link);

        $validated = $this->validatePayload($request, $socialLink);

        $socialLink->forceFill($validated)->save();

        return redirect()->route('admin.social-links.edit', $socialLink->id);
    }

    public function destroy(int $social_link): RedirectResponse
    {
        $socialLink = SocialLink::query()->findOrFail($social_link);
        $socialLink->delete();

        return redirect()->route('admin.social-links.index');
    }

    private function validatePayload(Request $request, ?SocialLink $socialLink = null): array
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:50'],
            'title' => ['nullable', 'string', 'max:190'],
            'url' => ['required', 'string', 'max:500'],
            'icon_media_id' => ['nullable', 'integer', 'min:1', 'exists:media,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable'],
        ]);

        return [
            'platform' => (string) $validated['platform'],
            'title' => isset($validated['title']) && $validated['title'] !== '' ? (string) $validated['title'] : null,
            'url' => (string) $validated['url'],
            'icon_media_id' => ($validated['icon_media_id'] ?? null) !== null && (string) $validated['icon_media_id'] !== '' ? (int) $validated['icon_media_id'] : null,
            'sort_order' => ($validated['sort_order'] ?? null) !== null && (string) $validated['sort_order'] !== '' ? (int) $validated['sort_order'] : 0,
            'is_active' => $request->boolean('is_active'),
            'meta' => $socialLink?->meta ?? [],
        ];
    }
}
