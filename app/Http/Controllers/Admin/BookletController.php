<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
            'institutions' => Category::query()
                ->where('type', 'institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->where('type', 'note')
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

        $booklet = Product::query()->create([
            'type' => 'note',
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

        $booklet->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

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

    public function show(int $booklet): RedirectResponse
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
            'institutions' => Category::query()
                ->where('type', 'institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->where('type', 'note')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
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
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'thumbnail_media_id' => $validated['thumbnail_media_id'] ?? $bookletModel->thumbnail_media_id,
            'institution_category_id' => $validated['institution_category_id'],
        ])->save();

        $bookletModel->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

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

        $hasExistingBookletFile = false;
        if ($product && $product->exists) {
            $hasExistingBookletFile = ProductPart::query()
                ->where('product_id', $product->id)
                ->where('part_type', 'file')
                ->whereNotNull('media_id')
                ->exists();
        }

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'institution_category_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('type', 'institution')],
            'category_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('type', 'note')],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
            'base_price' => [$shouldPublish ? 'required' : 'nullable', 'integer', 'min:0', 'max:2000000000'],
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
            'booklet_file' => [
                Rule::when($shouldPublish && ! $hasExistingBookletFile, ['required'], ['nullable']),
                'file',
                'max:102400',
                'mimes:pdf',
            ],
        ];

        $validated = $request->validate($rules);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'booklet-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        $thumbnailMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/covers');
        $bookletFileMedia = $this->storeUploadedMedia($request->file('booklet_file'), 'local', 'protected/booklets');

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
            'booklet_file_media_id' => $bookletFileMedia?->id,
            'institution_category_id' => ($validated['institution_category_id'] ?? null) !== null ? (int) $validated['institution_category_id'] : null,
            'category_id' => ($validated['category_id'] ?? null) !== null ? (int) $validated['category_id'] : null,
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
