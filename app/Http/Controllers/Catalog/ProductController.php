<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');

        $query = Product::query()
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video'])
            ->orderByDesc('published_at');

        if ($type && in_array($type, ['note', 'video'], true)) {
            $query->where('type', $type);
        }

        return view('catalog.products.index', [
            'products' => $query->get(),
            'activeType' => $type,
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
