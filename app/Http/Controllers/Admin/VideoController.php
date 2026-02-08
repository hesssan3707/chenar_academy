<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
                'currency' => 'IRR',
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
            'thumbnail_media_id' => null,
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        Video::query()->create([
            'product_id' => $product->id,
            'media_id' => null,
            'duration_seconds' => $validated['duration_seconds'],
            'meta' => [],
        ]);

        return redirect()->route('admin.videos.edit', $product->id);
    }

    public function show(int $video): View
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
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
        ])->save();

        Video::query()->updateOrCreate(['product_id' => $product->id], [
            'media_id' => null,
            'duration_seconds' => $validated['duration_seconds'],
            'meta' => [],
        ]);

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
            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'currency' => ['required', 'string', 'size:3'],
            'published_at' => ['nullable', 'string', 'max:32'],
            'duration_seconds' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
        ];

        $validated = $request->validate($rules);

        $slug = Str::slug((string) ($validated['slug'] ?? ''), '-');

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug !== '' ? $slug : Str::slug((string) $validated['title'], '-'),
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'currency' => strtoupper((string) $validated['currency']),
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'duration_seconds' => ($validated['duration_seconds'] ?? null) !== null && (string) $validated['duration_seconds'] !== '' ? (int) $validated['duration_seconds'] : null,
        ];
    }
}
