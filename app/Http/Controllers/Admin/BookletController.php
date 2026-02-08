<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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
                'currency' => 'IRR',
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
            'thumbnail_media_id' => null,
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

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
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
        ])->save();

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
            'published_at' => ['nullable', 'date'],
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
            'published_at' => ($validated['published_at'] ?? null) !== null && (string) $validated['published_at'] !== '' ? Carbon::parse((string) $validated['published_at']) : null,
        ];
    }
}
