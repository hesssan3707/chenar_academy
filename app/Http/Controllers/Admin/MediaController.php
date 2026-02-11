<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
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
            'disk' => ['required', 'string', Rule::in(['public', 'local'])],
            'file' => ['required', 'file', 'max:102400'],
        ]);

        $disk = (string) $validated['disk'];
        $file = $request->file('file');

        $media = $this->storeUploadedMedia($file, $disk, 'media');

        return redirect()->route('admin.media.show', $media->id);
    }

    public function wysiwyg(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required_without:upload', 'file', 'image', 'max:5120'],
            'upload' => ['required_without:file', 'file', 'image', 'max:5120'],
        ]);

        $file = $request->file('file') ?: $request->file('upload');
        $media = $this->storeUploadedMedia($file, 'public', 'media/wysiwyg');

        $url = route('media.stream', $media->id);

        $funcNum = (string) $request->input('CKEditorFuncNum', '');
        if ($funcNum !== '') {
            $escapedUrl = addslashes($url);
            $escapedFuncNum = addslashes($funcNum);

            return response(
                "<script>window.parent.CKEDITOR.tools.callFunction({$escapedFuncNum}, '{$escapedUrl}', '');</script>",
                200,
                ['Content-Type' => 'text/html; charset=utf-8'],
            );
        }

        return response()->json([
            'media_id' => $media->id,
            'url' => $url,
        ]);
    }

    public function show(int $media): View
    {
        $mediaModel = Media::query()->findOrFail($media);

        $path = str_replace('\\', '/', (string) ($mediaModel->path ?? ''));
        $mediaUrl = null;
        if ($path !== '') {
            $mediaUrl = route('admin.media.stream', $mediaModel->id);
        }

        return view('admin.media.show', [
            'title' => 'نمایش رسانه',
            'media' => $mediaModel,
            'mediaUrl' => $mediaUrl,
        ]);
    }

    public function stream(int $media)
    {
        $mediaModel = Media::query()->findOrFail($media);

        $diskName = (string) ($mediaModel->disk ?? '');
        $path = str_replace('\\', '/', (string) ($mediaModel->path ?? ''));
        if ($diskName === '' || $path === '') {
            abort(404);
        }

        if (str_contains($path, '..')) {
            abort(404);
        }

        $root = (string) config("filesystems.disks.{$diskName}.root", '');
        if ($root === '') {
            abort(404);
        }

        $absolutePath = rtrim($root, '\\/').DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));
        if (! is_file($absolutePath)) {
            abort(404);
        }

        $mime = (string) ($mediaModel->mime_type ?? '');
        $filename = (string) ($mediaModel->original_name ?: basename($path));

        return response()->file($absolutePath, [
            'Content-Type' => $mime !== '' ? $mime : 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
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

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): Media
    {
        $path = $file->store($directory, $disk);
        $path = str_replace('\\', '/', (string) $path);

        $width = null;
        $height = null;

        $mime = (string) ($file->getMimeType() ?: '');
        if ($mime !== '' && str_starts_with(strtolower($mime), 'image/')) {
            $imageSize = @getimagesize($file->getPathname());
            if (is_array($imageSize) && isset($imageSize[0], $imageSize[1])) {
                $width = is_numeric($imageSize[0]) ? (int) $imageSize[0] : null;
                $height = is_numeric($imageSize[1]) ? (int) $imageSize[1] : null;
            }
        }

        $sha1 = null;
        try {
            $sha1 = sha1_file($file->getPathname()) ?: null;
        } catch (\Throwable) {
            $sha1 = null;
        }

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => $sha1,
            'width' => $width,
            'height' => $height,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }
}
