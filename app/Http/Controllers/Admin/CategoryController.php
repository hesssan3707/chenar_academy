<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('type')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(40);

        return view('admin.categories.index', [
            'title' => 'دسته‌بندی‌ها',
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'title' => 'ایجاد دسته‌بندی',
            'category' => new Category([
                'is_active' => true,
                'sort_order' => 0,
            ]),
            'parents' => Category::query()->orderBy('type')->orderBy('title')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $category = Category::query()->create($validated);

        return redirect()->route('admin.categories.edit', $category->id);
    }

    public function show(int $category): View
    {
        return redirect()->route('admin.categories.edit', $category);
    }

    public function edit(int $category): View
    {
        $categoryModel = Category::query()->findOrFail($category);

        return view('admin.categories.form', [
            'title' => 'ویرایش دسته‌بندی',
            'category' => $categoryModel,
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
            'parent_id' => ['nullable', 'integer', 'min:1', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:190'],
            'icon_key' => ['nullable', 'string', 'max:50'],
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
            'icon_key' => isset($validated['icon_key']) && $validated['icon_key'] !== '' ? (string) $validated['icon_key'] : null,
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
