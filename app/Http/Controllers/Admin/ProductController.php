<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'currency' => $this->commerceCurrency(),
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

    public function show(int $product): RedirectResponse
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
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
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
        ]);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'product-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        return [
            'type' => (string) $validated['type'],
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'discount_type' => isset($validated['discount_type']) && $validated['discount_type'] !== '' ? (string) $validated['discount_type'] : null,
            'discount_value' => ($validated['discount_value'] ?? null) !== null && (string) $validated['discount_value'] !== '' ? (int) $validated['discount_value'] : null,
            'currency' => $this->commerceCurrency(),
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'meta' => $product?->meta ?? [],
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
}
