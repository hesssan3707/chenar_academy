<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductPart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookletController extends Controller
{
    public function index(): View
    {
        $booklets = Product::query()
            ->where('type', 'note')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.booklets.index', [
            'title' => 'جزوه‌ها',
            'booklets' => $booklets,
        ]);
    }

    public function create(): View
    {
        return view('admin.booklets.form', [
            'title' => 'ایجاد جزوه',
            'booklet' => new Product([
                'type' => 'note',
                'status' => 'draft',
                'currency' => $this->commerceCurrency(),
                'base_price' => 0,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $booklet = Product::query()->create([
            'type' => 'note',
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'description' => null,
            'thumbnail_media_id' => $validated['thumbnail_media_id'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        if (($validated['booklet_file_media_id'] ?? null) !== null) {
            ProductPart::query()->updateOrCreate([
                'product_id' => $booklet->id,
                'part_type' => 'file',
            ], [
                'title' => 'فایل جزوه',
                'sort_order' => 0,
                'media_id' => $validated['booklet_file_media_id'],
                'content' => null,
                'meta' => [],
            ]);
        }

        return redirect()->route('admin.booklets.edit', $booklet->id);
    }

    public function show(int $booklet): View
    {
        return redirect()->route('admin.booklets.edit', $booklet);
    }

    public function edit(int $booklet): View
    {
        $bookletModel = Product::query()
            ->where('type', 'note')
            ->findOrFail($booklet);

        return view('admin.booklets.form', [
            'title' => 'ویرایش جزوه',
            'booklet' => $bookletModel,
        ]);
    }

    public function update(Request $request, int $booklet): RedirectResponse
    {
        $bookletModel = Product::query()
            ->where('type', 'note')
            ->findOrFail($booklet);

        $validated = $this->validatePayload($request, $bookletModel);

        $bookletModel->forceFill([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'thumbnail_media_id' => $validated['thumbnail_media_id'] ?? $bookletModel->thumbnail_media_id,
        ])->save();

        if (($validated['booklet_file_media_id'] ?? null) !== null) {
            ProductPart::query()->updateOrCreate([
                'product_id' => $bookletModel->id,
                'part_type' => 'file',
            ], [
                'title' => 'فایل جزوه',
                'sort_order' => 0,
                'media_id' => $validated['booklet_file_media_id'],
                'content' => null,
                'meta' => [],
            ]);
        }

        return redirect()->route('admin.booklets.edit', $bookletModel->id);
    }

    public function destroy(int $booklet): RedirectResponse
    {
        $bookletModel = Product::query()
            ->where('type', 'note')
            ->findOrFail($booklet);

        $bookletModel->delete();

        return redirect()->route('admin.booklets.index');
    }

    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'published_at' => ['nullable', 'string', 'max:32'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
            'booklet_file' => ['nullable', 'file', 'max:102400'],
        ];

        $validated = $request->validate($rules);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'booklet-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        $thumbnailMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/covers');
        $bookletFileMedia = $this->storeUploadedMedia($request->file('booklet_file'), 'local', 'protected/booklets');

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'thumbnail_media_id' => $thumbnailMedia?->id,
            'booklet_file_media_id' => $bookletFileMedia?->id,
        ];
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
