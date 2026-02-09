<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::query()->orderByDesc('id')->paginate(40);

        return view('admin.banners.index', [
            'title' => 'بنرها',
            'banners' => $banners,
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.form', [
            'title' => 'ایجاد بنر',
            'banner' => new Banner([
                'position' => 'home',
                'is_active' => true,
                'sort_order' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $banner = Banner::query()->create($validated + ['meta' => []]);

        return redirect()->route('admin.banners.edit', $banner->id);
    }

    public function show(int $banner): RedirectResponse
    {
        return redirect()->route('admin.banners.edit', $banner);
    }

    public function edit(int $banner): View
    {
        $bannerModel = Banner::query()->findOrFail($banner);

        return view('admin.banners.form', [
            'title' => 'ویرایش بنر',
            'banner' => $bannerModel,
        ]);
    }

    public function update(Request $request, int $banner): RedirectResponse
    {
        $bannerModel = Banner::query()->findOrFail($banner);

        $validated = $this->validatePayload($request, $bannerModel);

        $bannerModel->forceFill($validated)->save();

        return redirect()->route('admin.banners.edit', $bannerModel->id);
    }

    public function destroy(int $banner): RedirectResponse
    {
        $bannerModel = Banner::query()->findOrFail($banner);
        $bannerModel->delete();

        return redirect()->route('admin.banners.index');
    }

    private function validatePayload(Request $request, ?Banner $banner = null): array
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:190'],
            'position' => ['required', 'string', 'max:50'],
            'image_media_id' => ['nullable', 'integer', 'min:1', 'exists:media,id'],
            'link_url' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'string', 'max:32'],
            'ends_at' => ['nullable', 'string', 'max:32'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable'],
        ]);

        return [
            'title' => isset($validated['title']) && $validated['title'] !== '' ? (string) $validated['title'] : null,
            'position' => (string) $validated['position'],
            'image_media_id' => ($validated['image_media_id'] ?? null) !== null && (string) $validated['image_media_id'] !== '' ? (int) $validated['image_media_id'] : null,
            'link_url' => isset($validated['link_url']) && $validated['link_url'] !== '' ? (string) $validated['link_url'] : null,
            'starts_at' => $this->parseDateTimeOrFail('starts_at', $validated['starts_at'] ?? null),
            'ends_at' => $this->parseDateTimeOrFail('ends_at', $validated['ends_at'] ?? null),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => ($validated['sort_order'] ?? null) !== null && (string) $validated['sort_order'] !== '' ? (int) $validated['sort_order'] : 0,
            'meta' => $banner?->meta ?? [],
        ];
    }
}
