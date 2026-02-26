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

        // 1. Fetch Categories for Videos
        $categories = Category::query()
            ->ofType('video')
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

        // 2. If Category Selected, Fetch Videos/Courses and Group by Institution
        if ($categorySlug) {
            $activeCategory = Category::query()
                ->ofType('video')
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->first();

            if ($activeCategory) {
                $videos = Product::query()
                    ->where('status', 'published')
                    ->whereIn('type', $types)
                    ->whereHas('categories', function ($q) use ($activeCategory) {
                        $q->where('categories.id', $activeCategory->id);
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
