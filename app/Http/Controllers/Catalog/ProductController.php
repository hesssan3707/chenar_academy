<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $institutionSlug = $request->query('institution');
        $categorySlug = $request->query('category');

        $query = Product::query()
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video'])
            ->orderByDesc('published_at');

        if ($type && in_array($type, ['note', 'video'], true)) {
            $query->where('type', $type);
        }

        $institutions = collect();
        $activeInstitution = null;
        $activeCategory = null;

        if ($type && in_array($type, ['note', 'video'], true)) {
            $institutions = Category::query()
                ->where('type', 'institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->with(['children' => function ($q) use ($type) {
                    $q->where('type', $type)
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('id');
                }])
                ->get();

            if ($institutionSlug) {
                $activeInstitution = Category::query()
                    ->where('type', 'institution')
                    ->where('slug', $institutionSlug)
                    ->where('is_active', true)
                    ->first();

                if ($activeInstitution) {
                    $query->whereHas('categories', function ($q) use ($activeInstitution, $type) {
                        $q->where('categories.type', $type)
                            ->where('categories.parent_id', $activeInstitution->id);
                    });
                }
            }

            if ($categorySlug) {
                $activeCategory = Category::query()
                    ->where('type', $type)
                    ->where('slug', $categorySlug)
                    ->where('is_active', true)
                    ->first();

                if ($activeCategory) {
                    $query->whereHas('categories', function ($q) use ($activeCategory) {
                        $q->where('categories.id', $activeCategory->id);
                    });
                }
            }
        }

        return view('catalog.products.index', [
            'products' => $query->get(),
            'activeType' => $type,
            'institutions' => $institutions,
            'activeInstitution' => $activeInstitution,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->whereIn('type', ['note', 'video'])
            ->firstOrFail();

        return view('catalog.products.show', ['product' => $product]);
    }
}
