<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookletController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $type = 'note';

        $categories = collect();
        $activeCategory = null;
        $groupedBooklets = collect();

        // 1. Fetch Categories for Booklets
        $categories = Category::query()
            ->where('type', $type)
            ->where('is_active', true)
            ->with('coverMedia')
            ->withCount(['products' => function ($q) use ($type) {
                $q->where('products.status', 'published')
                    ->where('products.type', $type);
            }])
            ->whereHas('products', function ($q) use ($type) {
                $q->where('products.status', 'published')
                    ->where('products.type', $type);
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // 2. If Category Selected, Fetch Booklets and Group by Institute
        if ($categorySlug) {
            $activeCategory = Category::query()
                ->where('type', $type)
                ->where('slug', $categorySlug)
                ->where('is_active', true)
                ->firstOrFail();

            $booklets = Product::query()
                ->where('status', 'published')
                ->where('type', $type)
                ->whereHas('categories', function ($q) use ($activeCategory) {
                    $q->where('categories.id', $activeCategory->id);
                })
                ->with(['thumbnailMedia', 'institutionCategory'])
                ->orderByDesc('published_at')
                ->get();

            // Group by Institution Category
            $groupedBooklets = $booklets->groupBy(function ($product) {
                return $product->institutionCategory ? $product->institutionCategory->title : 'سایر';
            });
        }

        return view('catalog.booklets.index', [
            'categories' => $categories,
            'activeCategory' => $activeCategory,
            'groupedBooklets' => $groupedBooklets,
        ]);
    }
}
