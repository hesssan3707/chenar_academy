<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->select('categories.*')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->with('categoryType')
            ->orderByRaw("case category_types.key when 'institution' then 1 when 'video' then 2 when 'note' then 3 when 'post' then 4 when 'ticket' then 5 else 99 end")
            ->orderBy('categories.sort_order')
            ->orderBy('categories.title')
            ->orderBy('categories.id')
            ->get();

        $categoryGroups = [];

        foreach ($categories->groupBy(fn (Category $category) => (string) ($category->categoryType?->key ?? '')) as $type => $typeCategories) {
            $type = (string) $type;
            $childrenByParent = [];
            foreach ($typeCategories as $category) {
                $key = $category->parent_id ?: 0;
                $childrenByParent[$key] ??= [];
                $childrenByParent[$key][] = $category;
            }

            foreach ($childrenByParent as $parentKey => $children) {
                usort($children, function (Category $a, Category $b): int {
                    $sortOrder = ((int) $a->sort_order) <=> ((int) $b->sort_order);
                    if ($sortOrder !== 0) {
                        return $sortOrder;
                    }

                    $titleOrder = strcmp((string) $a->title, (string) $b->title);
                    if ($titleOrder !== 0) {
                        return $titleOrder;
                    }

                    return ((int) $a->id) <=> ((int) $b->id);
                });

                $childrenByParent[$parentKey] = $children;
            }

            $categoryIds = $typeCategories->pluck('id')->map(fn ($id) => (int) $id)->all();

            $childrenIdsByParent = [];
            foreach ($typeCategories as $category) {
                $parentId = (int) ($category->parent_id ?: 0);
                $childrenIdsByParent[$parentId] ??= [];
                $childrenIdsByParent[$parentId][] = (int) $category->id;
            }

            if (in_array($type, ['note', 'video', 'post'], true)) {
                $directIds = [];

                if (in_array($type, ['note', 'video'], true)) {
                    $rows = DB::table('product_categories')
                        ->select(['category_id', 'product_id'])
                        ->whereIn('category_id', $categoryIds)
                        ->get();

                    foreach ($rows as $row) {
                        $categoryId = (int) $row->category_id;
                        $productId = (int) $row->product_id;
                        $directIds[$categoryId] ??= [];
                        $directIds[$categoryId][$productId] = true;
                    }
                } elseif ($type === 'post') {
                    $rows = DB::table('post_categories')
                        ->select(['category_id', 'post_id'])
                        ->whereIn('category_id', $categoryIds)
                        ->get();

                    foreach ($rows as $row) {
                        $categoryId = (int) $row->category_id;
                        $postId = (int) $row->post_id;
                        $directIds[$categoryId] ??= [];
                        $directIds[$categoryId][$postId] = true;
                    }
                }

                $memo = [];
                $collect = function (int $categoryId) use (&$collect, &$memo, $childrenIdsByParent, $directIds): array {
                    if (isset($memo[$categoryId])) {
                        return $memo[$categoryId];
                    }

                    $set = $directIds[$categoryId] ?? [];

                    foreach (($childrenIdsByParent[$categoryId] ?? []) as $childId) {
                        $set += $collect((int) $childId);
                    }

                    $memo[$categoryId] = $set;

                    return $set;
                };

                foreach ($typeCategories as $category) {
                    $count = count($collect((int) $category->id));
                    $category->setAttribute('related_count', $count);
                }
            }

            $flattened = [];
            $visited = [];

            $flatten = function (int $parentId, int $depth) use (&$flatten, &$flattened, &$childrenByParent, &$visited): void {
                foreach (($childrenByParent[$parentId] ?? []) as $child) {
                    if (isset($visited[$child->id])) {
                        continue;
                    }
                    $visited[$child->id] = true;

                    $flattened[] = [
                        'category' => $child,
                        'depth' => $depth,
                    ];

                    $flatten((int) $child->id, $depth + 1);
                }
            };

            $flatten(0, 0);

            foreach ($typeCategories as $category) {
                if (isset($visited[$category->id])) {
                    continue;
                }
                $flattened[] = [
                    'category' => $category,
                    'depth' => 0,
                ];
                $flatten((int) $category->id, 1);
            }

            $categoryGroups[(string) $type] = $flattened;
        }

        return view('admin.categories.index', [
            'title' => 'دسته‌بندی‌ها',
            'categoryGroups' => $categoryGroups,
            'typeTitles' => $this->categoryTypeTitles(),
        ]);
    }

    public function create(): View
    {
        $categoryTypes = $this->categoryTypes();

        return view('admin.categories.form', [
            'title' => 'ایجاد دسته‌بندی',
            'category' => new Category([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'categoryTypes' => $categoryTypes,
            'typeTitles' => $this->categoryTypeTitles(),
            'parents' => Category::query()->with('categoryType')->orderBy('category_type_id')->orderBy('title')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $category = Category::query()->create($validated);

        return redirect()->route('admin.categories.edit', $category->id);
    }

    public function show(int $category): RedirectResponse
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(int $category): View
    {
        $categoryModel = Category::query()->with('coverMedia')->findOrFail($category);

        $categoryTypes = $this->categoryTypes();

        $type = (string) ($categoryModel->type ?? '');
        if (in_array($type, ['note', 'video'], true)) {
            $descendantIds = $this->descendantCategoryIds($categoryModel->id, (int) ($categoryModel->category_type_id ?? 0));
            $hasProducts = DB::table('product_categories')->whereIn('category_id', $descendantIds)->exists();
            $categoryModel->setAttribute('related_count', $hasProducts ? 1 : 0);
        }

        return view('admin.categories.form', [
            'title' => 'ویرایش دسته‌بندی',
            'category' => $categoryModel,
            'categoryTypes' => $categoryTypes,
            'typeTitles' => $this->categoryTypeTitles(),
            'parents' => Category::query()
                ->where('id', '!=', $categoryModel->id)
                ->with('categoryType')
                ->orderBy('category_type_id')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        $categoryModel = Category::query()->findOrFail($category);

        $validated = $this->validatePayload($request, $categoryModel);

        $categoryModel->forceFill([
            'category_type_id' => $validated['category_type_id'],
            'parent_id' => $validated['parent_id'],
            'title' => $validated['title'],
            'icon_key' => $validated['icon_key'],
            'description' => $validated['description'],
            'cover_media_id' => $validated['remove_cover_image'] ? null : $validated['cover_media_id'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ])->save();

        return redirect()->route('admin.categories.edit', $categoryModel->id);
    }

    public function destroy(int $category): RedirectResponse
    {
        $categoryModel = Category::query()->findOrFail($category);
        $type = (string) ($categoryModel->type ?? '');
        if (in_array($type, ['note', 'video'], true)) {
            $descendantIds = $this->descendantCategoryIds($categoryModel->id, (int) ($categoryModel->category_type_id ?? 0));
            if (DB::table('product_categories')->whereIn('category_id', $descendantIds)->exists()) {
                return redirect()
                    ->route('admin.categories.edit', $categoryModel->id)
                    ->withErrors(['title' => 'این دسته‌بندی دارای محصول است و قابل حذف نیست. ابتدا محصولات را به دسته‌بندی دیگری منتقل کنید.']);
            }
        }
        $categoryModel->delete();

        return redirect()->route('admin.categories.index');
    }

    private function descendantCategoryIds(int $rootCategoryId, int $categoryTypeId): array
    {
        if ($rootCategoryId <= 0 || $categoryTypeId <= 0) {
            return [];
        }

        $categories = Category::query()
            ->select(['id', 'parent_id'])
            ->where('category_type_id', $categoryTypeId)
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

    private function validatePayload(Request $request, ?Category $category = null): array
    {
        $categoryTypeId = (int) $request->input('category_type_id', 0);
        $uniqueTitleRule = Rule::unique('categories', 'title')->where('category_type_id', $categoryTypeId);
        if ($category?->id) {
            $uniqueTitleRule->ignore($category->id);
        }

        $validated = $request->validate([
            'category_type_id' => ['required', 'integer', 'min:1', Rule::exists('category_types', 'id')],
            'parent_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('category_type_id', $categoryTypeId)],
            'title' => [
                'required',
                'string',
                'max:190',
                $uniqueTitleRule,
            ],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_cover_image' => ['nullable', 'in:0,1'],
            'is_active' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ], [
            'title.unique' => 'عنوان دسته‌بندی تکراری است.',
            'parent_id.exists' => 'دسته‌بندی والد انتخاب‌شده معتبر نیست.',
        ]);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'category-'.now()->format('YmdHis');
        }
        $slug = $category?->slug ?: $this->uniqueCategorySlug($categoryTypeId, $baseSlug);

        $coverMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/category-covers');

        return [
            'category_type_id' => (int) $validated['category_type_id'],
            'parent_id' => ($validated['parent_id'] ?? null) !== null ? (int) $validated['parent_id'] : null,
            'title' => trim((string) $validated['title']),
            'slug' => $slug,
            'icon_key' => $category?->icon_key,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'cover_media_id' => $coverMedia?->id ?? (int) ($category?->cover_media_id ?? 0) ?: null,
            'remove_cover_image' => $request->boolean('remove_cover_image'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => ($validated['sort_order'] ?? null) !== null ? (int) $validated['sort_order'] : 0,
        ];
    }

    private function categoryTypes()
    {
        if (! Schema::hasTable('category_types')) {
            return collect();
        }

        return CategoryType::query()->orderBy('id')->get(['id', 'key', 'title']);
    }

    private function categoryTypeTitles(): array
    {
        if (! Schema::hasTable('category_types')) {
            return [];
        }

        return CategoryType::query()->pluck('title', 'key')->all();
    }

    private function uniqueCategorySlug(int $categoryTypeId, string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()->where('category_type_id', $categoryTypeId)->where('slug', $slug)->exists()) {
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
}
