<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $types = ['video', 'course'];

        $categories = collect();
        $activeCategory = null;
        $groupedVideos = collect();

        // 1. Fetch Categories for Videos (merge video + course categories by title)
        $rawCategories = Category::query()
            ->whereIn('type', $types)
            ->where('is_active', true)
            ->with('coverMedia')
            ->withCount(['products' => function ($q) use ($types) {
                $q->where('products.status', 'published')
                    ->whereIn('products.type', $types);
            }])
            ->whereHas('products', function ($q) use ($types) {
                $q->where('products.status', 'published')
                    ->whereIn('products.type', $types);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Deduplicate categories by parent_id + title (prefer 'video' type)
        $categories = $rawCategories
            ->groupBy(function (Category $category) {
                return ((int) ($category->parent_id ?? 0)).'|'.trim((string) ($category->title ?? ''));
            })
            ->map(fn ($group) => $group->firstWhere('type', 'video') ?: $group->first())
            ->filter()
            ->values();

        // Recalculate products_count to include both video + course types for merged categories
        $categories->each(function ($category) use ($rawCategories, $types) {
            $matchingCategories = $rawCategories->filter(function ($c) use ($category) {
                return ((int) ($c->parent_id ?? 0)) === ((int) ($category->parent_id ?? 0))
                    && trim((string) ($c->title ?? '')) === trim((string) ($category->title ?? ''));
            });
            $totalCount = $matchingCategories->sum('products_count');
            $category->products_count = $totalCount;
        });

        // 2. If Category Selected, Fetch Videos/Courses and Group by Institution
        if ($categorySlug) {
            $activeCategories = Category::query()
                ->whereIn('type', $types)
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->get();

            $activeCategory = $activeCategories->firstWhere('type', 'video') ?: $activeCategories->first();

            if ($activeCategory) {
                // Get all matching category IDs (both video and course with same title/parent)
                $activeCategoryIds = Category::query()
                    ->whereIn('type', $types)
                    ->where('is_active', true)
                    ->where('parent_id', $activeCategory->parent_id)
                    ->where('title', $activeCategory->title)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                $videos = Product::query()
                    ->where('status', 'published')
                    ->whereIn('type', $types)
                    ->whereHas('categories', function ($q) use ($activeCategoryIds) {
                        $q->whereIn('categories.id', $activeCategoryIds);
                    })
                    ->with(['thumbnailMedia', 'institutionCategory'])
                    ->orderByDesc('published_at')
                    ->get();

                // Group by Institution Category
                $groupedVideos = $videos->groupBy(function ($product) {
                    return $product->institutionCategory ? $product->institutionCategory->title : 'سایر';
                });
            }
        }

        return view('catalog.videos.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'groupedVideos' => $groupedVideos,
        ]);
    }
}
