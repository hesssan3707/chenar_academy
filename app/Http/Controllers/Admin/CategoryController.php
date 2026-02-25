<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->orderByRaw("case type when 'video' then 1 when 'note' then 2 when 'course' then 3 when 'post' then 4 when 'ticket' then 5 else 99 end")
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id')
            ->get();

        $categoryGroups = [];

        foreach ($categories->groupBy('type') as $type => $typeCategories) {
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

            if (in_array($type, ['note', 'video', 'course', 'post'], true)) {
                $directIds = [];

                if (in_array($type, ['note', 'video', 'course'], true)) {
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
        ]);
    }

    public function create(): View
    {
        $defaultTypes = ['video', 'note', 'post', 'ticket', 'institution'];
        $existingTypes = Category::query()->select('type')->distinct()->orderBy('type')->pluck('type')->all();
        $types = array_values(array_unique(array_merge($defaultTypes, $existingTypes)));

        return view('admin.categories.form', [
            'title' => 'ایجاد دسته‌بندی',
            'category' => new Category([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'types' => $types,
            'parents' => Category::query()->orderBy('type')->orderBy('title')->orderBy('id')->get(),
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

        $defaultTypes = ['video', 'note', 'post', 'ticket', 'institution'];
        $existingTypes = Category::query()->select('type')->distinct()->orderBy('type')->pluck('type')->all();
        $types = array_values(array_unique(array_merge($defaultTypes, $existingTypes)));

        $type = (string) ($categoryModel->type ?? '');
        if (in_array($type, ['note', 'video', 'course'], true)) {
            $descendantIds = $this->descendantCategoryIds($categoryModel->id, $type);
            $hasProducts = DB::table('product_categories')->whereIn('category_id', $descendantIds)->exists();
            $categoryModel->setAttribute('related_count', $hasProducts ? 1 : 0);
        }

        return view('admin.categories.form', [
            'title' => 'ویرایش دسته‌بندی',
            'category' => $categoryModel,
            'types' => $types,
            'parents' => Category::query()
                ->where('id', '!=', $categoryModel->id)
                ->orderBy('type')
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
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'],
            'title' => $validated['title'],
            'icon_key' => $validated['icon_key'],
            'description' => $validated['description'],
            'cover_media_id' => $validated['cover_media_id'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ])->save();

        return redirect()->route('admin.categories.edit', $categoryModel->id);
    }

    public function destroy(int $category): RedirectResponse
    {
        $categoryModel = Category::query()->findOrFail($category);
        $type = (string) ($categoryModel->type ?? '');
        if (in_array($type, ['note', 'video', 'course'], true)) {
            $descendantIds = $this->descendantCategoryIds($categoryModel->id, $type);
            if (DB::table('product_categories')->whereIn('category_id', $descendantIds)->exists()) {
                return redirect()
                    ->route('admin.categories.edit', $categoryModel->id)
                    ->withErrors(['title' => 'این دسته‌بندی دارای محصول است و قابل حذف نیست. ابتدا محصولات را به دسته‌بندی دیگری منتقل کنید.']);
            }
        }
        $categoryModel->delete();

        return redirect()->route('admin.categories.index');
    }

    private function descendantCategoryIds(int $rootCategoryId, string $type): array
    {
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

    private function validatePayload(Request $request, ?Category $category = null): array
    {
        $type = trim((string) $request->input('type', ''));
        $uniqueTitleRule = Rule::unique('categories', 'title')->where('type', $type);
        if ($category?->id) {
            $uniqueTitleRule->ignore($category->id);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:20'],
            'parent_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('type', $type)],
            'title' => [
                'required',
                'string',
                'max:190',
                $uniqueTitleRule,
            ],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
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
        $slug = $category?->slug ?: $this->uniqueCategorySlug($type, $baseSlug);

        $coverMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/category-covers');

        return [
            'type' => (string) $validated['type'],
            'parent_id' => ($validated['parent_id'] ?? null) !== null ? (int) $validated['parent_id'] : null,
            'title' => trim((string) $validated['title']),
            'slug' => $slug,
            'icon_key' => $category?->icon_key,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'cover_media_id' => $coverMedia?->id ?? (int) ($category?->cover_media_id ?? 0) ?: null,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => ($validated['sort_order'] ?? null) !== null ? (int) $validated['sort_order'] : 0,
        ];
    }

    private function uniqueCategorySlug(string $type, string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()->where('type', $type)->where('slug', $slug)->exists()) {
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
