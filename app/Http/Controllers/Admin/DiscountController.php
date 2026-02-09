<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiscountController extends Controller
{
    public function category(): View
    {
        return view('admin.discounts.category', [
            'title' => 'تخفیف گروهی محصولات',
            'categories' => Category::query()
                ->where('is_active', true)
                ->whereNotIn('type', ['institution', 'post'])
                ->orderBy('type')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function applyCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'discount_type' => ['required', 'string', Rule::in(['percent', 'amount'])],
            'discount_value' => [
                'required',
                'integer',
                'min:0',
                'max:2000000000',
                Rule::when($request->input('discount_type') === 'percent', ['max:100']),
            ],
        ]);

        $categoryId = (int) $validated['category_id'];
        $discountType = (string) $validated['discount_type'];
        $discountValue = (int) $validated['discount_value'];

        $affected = DB::transaction(function () use ($categoryId, $discountType, $discountValue) {
            return Product::query()
                ->whereHas('categories', fn ($q) => $q->where('categories.id', $categoryId))
                ->update([
                    'sale_price' => null,
                    'discount_type' => $discountType,
                    'discount_value' => $discountValue,
                ]);
        });

        return redirect()->route('admin.discounts.category')->with('toast', [
            'type' => 'success',
            'title' => 'اعمال شد',
            'message' => 'تخفیف برای '.(int) $affected.' محصول اعمال شد.',
        ]);
    }
}
