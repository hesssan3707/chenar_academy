<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = (int) $request->query('category', 0);

        $productsQuery = Product::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        $activeCategory = null;
        $categoryOptions = collect();
        $activeCategoryType = null;

        if ($categoryId > 0) {
            $activeCategory = Category::query()->find($categoryId);

            if ($activeCategory) {
                $activeCategoryType = (string) ($activeCategory->type ?? '');
                $descendantIds = $this->descendantCategoryIds($activeCategory->id, $activeCategoryType);

                $productsQuery->whereHas('categories', function ($q) use ($descendantIds) {
                    $q->whereIn('categories.id', $descendantIds);
                });

                if (in_array($activeCategoryType, ['note', 'video', 'course'], true)) {
                    $categoryOptions = Category::query()
                        ->where('type', $activeCategoryType)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('title')
                        ->orderBy('id')
                        ->get();

                    $productsQuery->with('categories');
                }
            }
        }

        $products = $productsQuery->paginate(40)->withQueryString();

        return view('admin.products.index', [
            'title' => 'محصولات',
            'products' => $products,
            'activeCategory' => $activeCategory,
            'activeCategoryType' => $activeCategoryType,
            'categoryOptions' => $categoryOptions,
        ]);
    }

    public function updateCategory(Request $request, int $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'min:1', 'exists:categories,id'],
        ]);

        $categoryId = (int) $validated['category_id'];
        $category = Category::query()->findOrFail($categoryId);
        $type = (string) ($category->type ?? '');

        abort_unless(in_array($type, ['note', 'video', 'course'], true), 403);

        $productModel = Product::query()->findOrFail($product);

        DB::transaction(function () use ($productModel, $categoryId, $type) {
            $existingIds = $productModel->categories()
                ->where('categories.type', $type)
                ->pluck('categories.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if ($existingIds !== []) {
                $productModel->categories()->detach($existingIds);
            }

            $productModel->categories()->syncWithoutDetaching([$categoryId]);
        });

        return redirect()->back();
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
            'institutions' => Category::query()
                ->where('type', 'institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->whereIn('type', ['note', 'video', 'course'])
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $categoryId = (int) $validated['category_id'];
        unset($validated['category_id']);

        $product = DB::transaction(function () use ($validated, $categoryId) {
            $model = Product::query()->create($validated);
            $this->syncProductCategory($model, $categoryId);

            return $model;
        });

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
            'institutions' => Category::query()
                ->where('type', 'institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->whereIn('type', ['note', 'video', 'course'])
                ->where('is_active', true)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        $productModel = Product::query()->findOrFail($product);

        $validated = $this->validatePayload($request, $productModel);

        $categoryId = (int) $validated['category_id'];
        unset($validated['category_id']);

        DB::transaction(function () use ($productModel, $validated, $categoryId) {
            $productModel->forceFill($validated)->save();
            $this->syncProductCategory($productModel, $categoryId);
        });

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
        $inputType = trim((string) $request->input('type', ''));

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:20'],
            'title' => ['required', 'string', 'max:180'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'institution_category_id' => ['required', 'integer', 'min:1', Rule::exists('categories', 'id')->where('type', 'institution')],
            'category_id' => [
                'required',
                'integer',
                'min:1',
                Rule::exists('categories', 'id')->where(function ($query) use ($inputType) {
                    if (in_array($inputType, ['note', 'video', 'course'], true)) {
                        $query->where('type', $inputType);

                        return;
                    }

                    $query->whereIn('type', ['note', 'video', 'course']);
                }),
            ],
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
            'institution_category_id' => (int) $validated['institution_category_id'],
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'discount_type' => isset($validated['discount_type']) && $validated['discount_type'] !== '' ? (string) $validated['discount_type'] : null,
            'discount_value' => ($validated['discount_value'] ?? null) !== null && (string) $validated['discount_value'] !== '' ? (int) $validated['discount_value'] : null,
            'currency' => $this->commerceCurrency(),
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'meta' => $product?->meta ?? [],
            'category_id' => (int) $validated['category_id'],
        ];
    }

    private function syncProductCategory(Product $product, int $categoryId): void
    {
        if ($categoryId <= 0) {
            return;
        }

        $category = Category::query()->findOrFail($categoryId);
        $categoryType = (string) ($category->type ?? '');
        abort_unless(in_array($categoryType, ['note', 'video', 'course'], true), 403);

        $existingIds = $product->categories()
            ->whereIn('categories.type', ['note', 'video', 'course'])
            ->pluck('categories.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($existingIds !== []) {
            $product->categories()->detach($existingIds);
        }

        $product->categories()->syncWithoutDetaching([$categoryId]);
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

    private function descendantCategoryIds(int $rootCategoryId, string $type): array
    {
        if ($rootCategoryId <= 0 || $type === '') {
            return [];
        }

        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->where('type', $type)
            ->get();

        $childrenByParent = [];
        foreach ($categories as $category) {
            $parentId = (int) ($category->parent_id ?: 0);
            $childrenByParent[$parentId] ??= [];
            $childrenByParent[$parentId][] = (int) $category->id;
        }

        $result = [];
        $stack = [(int) $rootCategoryId];
        $seen = [];

        while ($stack !== []) {
            $current = array_pop($stack);
            if ($current <= 0 || isset($seen[$current])) {
                continue;
            }
            $seen[$current] = true;
            $result[] = $current;

            foreach (($childrenByParent[$current] ?? []) as $childId) {
                $stack[] = (int) $childId;
            }
        }

        return $result;
    }
}
