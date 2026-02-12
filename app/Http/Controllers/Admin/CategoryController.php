<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->orderBy('id')
            ->get();

        $categoryGroups = [];

        foreach ($categories->groupBy('type') as $type => $typeCategories) {
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
        $defaultTypes = ['video', 'note', 'institution', 'post'];
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
        $categoryModel = Category::query()->findOrFail($category);

        $defaultTypes = ['video', 'note', 'institution', 'post'];
        $existingTypes = Category::query()->select('type')->distinct()->orderBy('type')->pluck('type')->all();
        $types = array_values(array_unique(array_merge($defaultTypes, $existingTypes)));

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
            'icon_key' => $categoryModel->icon_key,
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ])->save();

        return redirect()->route('admin.categories.edit', $categoryModel->id);
    }

    public function destroy(int $category): RedirectResponse
    {
        $categoryModel = Category::query()->findOrFail($category);
        $categoryModel->delete();

        return redirect()->route('admin.categories.index');
    }

    private function validatePayload(Request $request, ?Category $category = null): array
    {
        $type = trim((string) $request->input('type', ''));

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:20'],
            'parent_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('type', $type)],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'category-'.now()->format('YmdHis');
        }
        $slug = $category?->slug ?: $this->uniqueCategorySlug($type, $baseSlug);

        return [
            'type' => (string) $validated['type'],
            'parent_id' => ($validated['parent_id'] ?? null) !== null ? (int) $validated['parent_id'] : null,
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'icon_key' => $category?->icon_key,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
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
}
