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

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.products.index', [
            'title' => 'محصولات',
            'products' => $products,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'title' => 'ایجاد محصول',
            'product' => new Product([
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

        $product = Product::query()->create($validated);

        return redirect()->route('admin.products.edit', $product->id);
    }

    public function show(int $product): View
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(int $product): View
    {
        $productModel = Product::query()->findOrFail($product);

        return view('admin.products.form', [
            'title' => 'ویرایش محصول',
            'product' => $productModel,
        ]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $productModel = Product::query()->findOrFail($product);

        $validated = $this->validatePayload($request, $productModel);

        $productModel->forceFill($validated)->save();

        return redirect()->route('admin.products.edit', $productModel->id);
    }

    public function destroy(int $product): RedirectResponse
    {
        $productModel = Product::query()->findOrFail($product);
        $productModel->delete();

        return redirect()->route('admin.products.index');
    }

    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'currency' => ['required', 'string', 'size:3'],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = Str::slug((string) ($validated['slug'] ?? ''), '-');

        return [
            'type' => (string) $validated['type'],
            'title' => (string) $validated['title'],
            'slug' => $slug !== '' ? $slug : Str::slug((string) $validated['title'], '-'),
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'currency' => strtoupper((string) $validated['currency']),
            'published_at' => ($validated['published_at'] ?? null) !== null && (string) $validated['published_at'] !== '' ? Carbon::parse((string) $validated['published_at']) : null,
            'meta' => $product?->meta ?? [],
        ];
    }
}
