<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        $durationSeconds = null;
        if (($validated['media_id'] ?? null) !== null) {
            $fullMedia = Media::query()->findOrFail((int) $validated['media_id']);
            $durationSeconds = $this->extractVideoDurationSecondsOrFail($fullMedia);
        }

        Video::query()->create([
            'product_id' => $product->id,
            'media_id' => $validated['media_id'],
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
            'thumbnail_media_id' => $validated['thumbnail_media_id'] ?? $product->thumbnail_media_id,
        ])->save();

        $videoPayload = [
            'meta' => [],
        ];

        if (($validated['media_id'] ?? null) !== null) {
            $fullMedia = Media::query()->findOrFail((int) $validated['media_id']);
            $videoPayload['duration_seconds'] = $this->extractVideoDurationSecondsOrFail($fullMedia);
            $videoPayload['media_id'] = $validated['media_id'];
        }

        if (($validated['preview_media_id'] ?? null) !== null) {
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
        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000', 'prohibited_with:discount_type,discount_value'],
            'discount_type' => ['nullable', 'string', Rule::in(['percent', 'amount']), 'required_with:discount_value', 'prohibited_with:sale_price'],
            'discount_value' => [
                'nullable',
                'integer',
                'min:0',
                'max:2000000000',
                'required_with:discount_type',
                'prohibited_with:sale_price',
                Rule::when($request->input('discount_type') === 'percent', ['max:100']),
            ],
            'published_at' => ['nullable', 'string', 'max:32'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
            'preview_video' => ['nullable', 'file', 'max:102400'],
            'video_file' => ['nullable', 'file', 'max:512000'],
        ];

        $validated = $request->validate($rules);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'video-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        $thumbnailMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/covers');
        $previewMedia = $this->storeUploadedMedia($request->file('preview_video'), 'local', 'protected/previews');
        $fullMedia = $this->storeUploadedMedia($request->file('video_file'), 'local', 'protected/videos');

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'discount_type' => isset($validated['discount_type']) && $validated['discount_type'] !== '' ? (string) $validated['discount_type'] : null,
            'discount_value' => ($validated['discount_value'] ?? null) !== null && (string) $validated['discount_value'] !== '' ? (int) $validated['discount_value'] : null,
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'thumbnail_media_id' => $thumbnailMedia?->id,
            'preview_media_id' => $previewMedia?->id,
            'media_id' => $fullMedia?->id,
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
        $absolutePath = Storage::disk($media->disk)->path($media->path);

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

        $path = Storage::disk($disk)->putFile($directory, $file);

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
}
