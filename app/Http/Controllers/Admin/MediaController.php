<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        $mediaItems = Media::query()->orderByDesc('id')->paginate(40);

        return view('admin.media.index', [
            'title' => 'رسانه‌ها',
            'mediaItems' => $mediaItems,
        ]);
    }

    public function create(): View
    {
        return view('admin.media.form', [
            'title' => 'آپلود رسانه',
            'media' => new Media([
                'disk' => 'public',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'disk' => ['required', 'string', 'max:50'],
            'path' => ['required', 'string', 'max:500'],
            'original_name' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'integer', 'min:0', 'max:9223372036854775807'],
        ]);

        $media = Media::query()->create([
            'uploaded_by_user_id' => $request->user()?->id,
            'disk' => (string) $validated['disk'],
            'path' => (string) $validated['path'],
            'original_name' => isset($validated['original_name']) && $validated['original_name'] !== '' ? (string) $validated['original_name'] : null,
            'mime_type' => isset($validated['mime_type']) && $validated['mime_type'] !== '' ? (string) $validated['mime_type'] : null,
            'size' => ($validated['size'] ?? null) !== null && (string) $validated['size'] !== '' ? (int) $validated['size'] : null,
            'meta' => [],
        ]);

        return redirect()->route('admin.media.show', $media->id);
    }

    public function show(int $media): View
    {
        $mediaModel = Media::query()->findOrFail($media);

        return view('admin.media.show', [
            'title' => 'نمایش رسانه',
            'media' => $mediaModel,
        ]);
    }

    public function edit(int $media): View
    {
        $mediaModel = Media::query()->findOrFail($media);

        return view('admin.media.form', [
            'title' => 'ویرایش رسانه',
            'media' => $mediaModel,
        ]);
    }

    public function update(Request $request, int $media): RedirectResponse
    {
        $mediaModel = Media::query()->findOrFail($media);

        $validated = $request->validate([
            'disk' => ['required', 'string', 'max:50'],
            'path' => ['required', 'string', 'max:500'],
            'original_name' => ['nullable', 'string', 'max:255'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'integer', 'min:0', 'max:9223372036854775807'],
        ]);

        $mediaModel->forceFill([
            'disk' => (string) $validated['disk'],
            'path' => (string) $validated['path'],
            'original_name' => isset($validated['original_name']) && $validated['original_name'] !== '' ? (string) $validated['original_name'] : null,
            'mime_type' => isset($validated['mime_type']) && $validated['mime_type'] !== '' ? (string) $validated['mime_type'] : null,
            'size' => ($validated['size'] ?? null) !== null && (string) $validated['size'] !== '' ? (int) $validated['size'] : null,
        ])->save();

        return redirect()->route('admin.media.edit', $mediaModel->id);
    }

    public function destroy(int $media): RedirectResponse
    {
        $mediaModel = Media::query()->findOrFail($media);
        $mediaModel->delete();

        return redirect()->route('admin.media.index');
    }
}
