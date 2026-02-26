<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(): View
    {
        $videos = Product::query()
            ->where('type', 'video')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.videos.index', [
            'title' => 'ویدیوها',
            'videos' => $videos,
        ]);
    }

    public function create(): View
    {
        return view('admin.videos.form', [
            'title' => 'ایجاد ویدیو',
            'videoProduct' => new Product([
                'type' => 'video',
                'status' => 'draft',
                'currency' => $this->commerceCurrency(),
                'base_price' => 0,
            ]),
            'video' => new Video([
                'duration_seconds' => null,
            ]),
            'institutions' => Category::query()
                ->ofType('institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->ofType('video')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $product = Product::query()->create([
            'type' => 'video',
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'description' => null,
            'thumbnail_media_id' => $validated['thumbnail_media_id'],
            'institution_category_id' => $validated['institution_category_id'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        $product->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

        $durationSeconds = null;
        if (($validated['media_id'] ?? null) !== null) {
            $fullMedia = Media::query()->findOrFail((int) $validated['media_id']);
            $durationSeconds = $this->extractVideoDurationSecondsOrFail($fullMedia);
        }

        Video::query()->create([
            'product_id' => $product->id,
            'media_id' => $validated['media_id'],
            'video_url' => $validated['video_url'],
            'preview_media_id' => $validated['preview_media_id'],
            'duration_seconds' => $durationSeconds,
            'meta' => [],
        ]);

        return redirect()->route('admin.videos.edit', $product->id);
    }

    public function show(int $video): RedirectResponse
    {
        return redirect()->route('admin.videos.edit', $video);
    }

    public function edit(int $video): View
    {
        $product = Product::query()
            ->where('type', 'video')
            ->findOrFail($video);

        $videoModel = Video::query()->where('product_id', $product->id)->first() ?: new Video([
            'product_id' => $product->id,
            'duration_seconds' => null,
        ]);

        return view('admin.videos.form', [
            'title' => 'ویرایش ویدیو',
            'videoProduct' => $product,
            'video' => $videoModel,
            'institutions' => Category::query()
                ->ofType('institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->ofType('video')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, int $video): RedirectResponse
    {
        $product = Product::query()
            ->where('type', 'video')
            ->findOrFail($video);

        $validated = $this->validatePayload($request, $product);

        $product->forceFill([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'thumbnail_media_id' => $validated['remove_cover_image']
                ? null
                : ($validated['thumbnail_media_id'] ?? $product->thumbnail_media_id),
            'institution_category_id' => $validated['institution_category_id'],
        ])->save();

        $product->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

        $videoPayload = [
            'meta' => [],
        ];
        if (($validated['media_id'] ?? null) !== null) {
            $fullMedia = Media::query()->findOrFail((int) $validated['media_id']);
            $videoPayload['duration_seconds'] = $this->extractVideoDurationSecondsOrFail($fullMedia);
            $videoPayload['media_id'] = $validated['media_id'];
            $videoPayload['video_url'] = null;
        } else {
            $videoPayload['media_id'] = null;
            $videoPayload['duration_seconds'] = null;
            $videoPayload['video_url'] = $validated['video_url'];
        }

        if ($validated['remove_preview_video']) {
            $videoPayload['preview_media_id'] = null;
        } elseif (($validated['preview_media_id'] ?? null) !== null) {
            $videoPayload['preview_media_id'] = $validated['preview_media_id'];
        }

        Video::query()->updateOrCreate(['product_id' => $product->id], $videoPayload);

        return redirect()->route('admin.videos.edit', $product->id);
    }

    public function destroy(int $video): RedirectResponse
    {
        $product = Product::query()
            ->where('type', 'video')
            ->findOrFail($video);

        $product->delete();

        return redirect()->route('admin.videos.index');
    }

    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $intent = trim((string) $request->input('intent', ''));

        $inputStatus = trim((string) $request->input('status', ''));
        $shouldPublish = $intent === 'publish' || ($intent === '' && $inputStatus === 'published');

        $status = match ($intent) {
            'publish' => 'published',
            'draft' => 'draft',
            '' => ($inputStatus !== '' ? $inputStatus : (string) ($product?->status ?? 'draft')),
            default => (string) ($product?->status ?? 'draft'),
        };
        if ($status !== 'draft' && $status !== 'published') {
            $status = 'draft';
        }

        $existingVideoMediaId = null;
        $existingVideoUrl = null;
        if ($product && $product->exists) {
            $existingVideoMediaId = Video::query()->where('product_id', $product->id)->value('media_id');
            $existingVideoUrl = Video::query()->where('product_id', $product->id)->value('video_url');
        }

        $removeVideoFile = $request->boolean('remove_video_file');
        if ($removeVideoFile) {
            $existingVideoMediaId = null;
        }

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'institution_category_id' => ['required', 'integer', 'min:1', Rule::exists('categories', 'id')->where('category_type_id', Category::typeId('institution'))],
            'category_id' => ['required', 'integer', 'min:1', Rule::exists('categories', 'id')->where('category_type_id', Category::typeId('video'))],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
            'base_price' => [$shouldPublish ? 'required' : 'nullable', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000', 'prohibits:discount_type,discount_value'],
            'discount_type' => ['nullable', 'string', Rule::in(['percent', 'amount']), 'required_with:discount_value', 'prohibits:sale_price'],
            'discount_value' => [
                'nullable',
                'integer',
                'min:0',
                'max:2000000000',
                'required_with:discount_type',
                'prohibits:sale_price',
                Rule::when($request->input('discount_type') === 'percent', ['max:100']),
            ],
            'published_at' => ['nullable', 'string', 'max:32'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
            'preview_video' => ['nullable', 'file', 'max:102400'],
            'remove_cover_image' => ['nullable', 'in:0,1'],
            'remove_preview_video' => ['nullable', 'in:0,1'],
            'remove_video_file' => ['nullable', 'in:0,1'],
            'video_url' => ['nullable', 'string', 'max:2048', 'url', 'prohibits:video_file'],
            'video_file' => ['nullable', 'file', 'max:1048576', 'prohibits:video_url'],
        ];

        $validated = $request->validate($rules);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'video-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        if ($request->hasFile('preview_video') || $request->hasFile('video_file')) {
            set_time_limit(0);
        }

        $thumbnailMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/covers');
        $previewMedia = $this->storeUploadedMedia($request->file('preview_video'), 'videos', 'protected/previews');
        $fullMedia = $this->storeUploadedMedia($request->file('video_file'), 'videos', 'protected/videos');

        $videoUrlInput = trim((string) $request->input('video_url', ''));
        $videoUrl = $videoUrlInput !== '' ? $videoUrlInput : null;

        $effectiveMediaId = $fullMedia?->id ?? ($removeVideoFile ? null : ($existingVideoMediaId ? (int) $existingVideoMediaId : null));
        $effectiveVideoUrl = $videoUrl;

        if (($fullMedia?->id ?? null) !== null && $effectiveVideoUrl !== null) {
            throw ValidationException::withMessages([
                'video_url' => ['ÙÙ‚Ø· ÛŒÚ©ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÛŒØ§ ÙØ§ÛŒÙ„ ÙˆÛŒØ¯ÛŒÙˆ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯.'],
                'video_file' => ['ÙÙ‚Ø· ÛŒÚ©ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÛŒØ§ ÙØ§ÛŒÙ„ ÙˆÛŒØ¯ÛŒÙˆ Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯.'],
            ]);
        }

        if (($fullMedia?->id ?? null) !== null) {
            $effectiveVideoUrl = null;
        } elseif ($effectiveVideoUrl === null && is_string($existingVideoUrl) && trim($existingVideoUrl) !== '') {
            $effectiveVideoUrl = trim($existingVideoUrl);
        }

        if ($effectiveVideoUrl !== null) {
            $effectiveMediaId = null;
        }

        if (($effectiveMediaId === null && $effectiveVideoUrl === null) || ($effectiveMediaId !== null && $effectiveVideoUrl !== null)) {
            throw ValidationException::withMessages([
                'video_url' => ['Ø¨Ø§ÛŒØ¯ Ø¯Ù‚ÛŒÙ‚Ø§Ù‹ ÛŒÚ©ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÛŒØ§ ÙØ§ÛŒÙ„ ÙˆÛŒØ¯ÛŒÙˆ ØªØ¹ÛŒÛŒÙ† Ø´ÙˆØ¯.'],
                'video_file' => ['Ø¨Ø§ÛŒØ¯ Ø¯Ù‚ÛŒÙ‚Ø§Ù‹ ÛŒÚ©ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÛŒØ§ ÙØ§ÛŒÙ„ ÙˆÛŒØ¯ÛŒÙˆ ØªØ¹ÛŒÛŒÙ† Ø´ÙˆØ¯.'],
            ]);
        }

        $publishedAt = $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null);
        if ($shouldPublish && $publishedAt === null) {
            $publishedAt = now(config('app.timezone'));
        }

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'status' => $status,
            'base_price' => (int) ($validated['base_price'] ?? ($product?->base_price ?? 0)),
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'discount_type' => isset($validated['discount_type']) && $validated['discount_type'] !== '' ? (string) $validated['discount_type'] : null,
            'discount_value' => ($validated['discount_value'] ?? null) !== null && (string) $validated['discount_value'] !== '' ? (int) $validated['discount_value'] : null,
            'published_at' => $status === 'published' ? $publishedAt : null,
            'thumbnail_media_id' => $thumbnailMedia?->id,
            'preview_media_id' => $previewMedia?->id,
            'media_id' => $effectiveMediaId,
            'video_url' => $effectiveVideoUrl,
            'remove_cover_image' => $request->boolean('remove_cover_image'),
            'remove_preview_video' => $request->boolean('remove_preview_video'),
            'institution_category_id' => (int) $validated['institution_category_id'],
            'category_id' => (int) $validated['category_id'],
        ];
    }

    private function extractVideoDurationSecondsOrFail(Media $media): int
    {
        $seconds = $this->extractVideoDurationSeconds($media);
        if ($seconds === null) {
            throw ValidationException::withMessages([
                'video_file' => ['امکان استخراج مدت زمان ویدیو وجود ندارد.'],
            ]);
        }

        $media->forceFill(['duration_seconds' => $seconds])->save();

        return $seconds;
    }

    private function extractVideoDurationSeconds(Media $media): ?int
    {
        $disk = Storage::disk($media->disk);

        $absolutePath = null;
        $tempPath = null;

        try {
            $absolutePath = $this->resolveLocalDiskPath($media->disk, $media->path);

            if ($absolutePath === null) {
                $stream = $disk->readStream($media->path);
                if (! is_resource($stream)) {
                    return null;
                }

                $tempBase = tempnam(sys_get_temp_dir(), 'chenar_video_');
                if (! is_string($tempBase) || $tempBase === '') {
                    fclose($stream);

                    return null;
                }

                $tempPath = $tempBase.'.bin';
                @rename($tempBase, $tempPath);

                $out = fopen($tempPath, 'wb');
                if (! is_resource($out)) {
                    fclose($stream);

                    return null;
                }

                stream_copy_to_stream($stream, $out);
                fclose($out);
                fclose($stream);

                $absolutePath = $tempPath;
            }

            $result = Process::timeout(30)->run([
                'ffprobe',
                '-v',
                'error',
                '-show_entries',
                'format=duration',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $absolutePath,
            ]);

            if (! $result->successful()) {
                return null;
            }

            $raw = trim($result->output());
            if ($raw === '' || ! is_numeric($raw)) {
                return null;
            }

            $seconds = (int) round((float) $raw);

            return $seconds >= 0 ? $seconds : null;
        } finally {
            if (is_string($tempPath) && $tempPath !== '') {
                @unlink($tempPath);
            }
        }
    }

    private function uniqueProductSlug(string $baseSlug, ?int $ignoreProductId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreProductId, fn ($q) => $q->where('id', '!=', $ignoreProductId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): ?Media
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($directory, $disk);

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }

    private function resolveLocalDiskPath(string $disk, string $path): ?string
    {
        $root = config("filesystems.disks.$disk.root");
        if (! is_string($root) || $root === '') {
            return null;
        }

        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));

        return rtrim($root, '/\\').DIRECTORY_SEPARATOR.$relativePath;
    }
}
