<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $coupons = Coupon::query()->orderByDesc('id')->paginate(40);

        $reportCouponId = (int) $request->query('report', 0);
        $reportCoupon = $reportCouponId > 0 ? Coupon::query()->find($reportCouponId) : null;
        $usageStats = $reportCoupon ? $this->usageStats($reportCoupon) : null;
        $redemptions = $reportCoupon
            ? CouponRedemption::query()
                ->where('coupon_id', $reportCoupon->id)
                ->with(['user', 'order'])
                ->orderByDesc('redeemed_at')
                ->orderByDesc('id')
                ->paginate(40, ['*'], 'report_page')
            : null;

        return view('admin.coupons.index', [
            'title' => 'کدهای تخفیف',
            'coupons' => $coupons,
            'reportCoupon' => $reportCoupon,
            'usageStats' => $usageStats,
            'redemptions' => $redemptions,
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'title' => 'ایجاد کد تخفیف',
            'coupon' => new Coupon([
                'discount_type' => 'percent',
                'discount_value' => 10,
                'is_active' => true,
                'used_count' => 0,
            ]),
            'products' => $this->couponProductsList(),
            'selectedProductIds' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $coupon = Coupon::query()->create($validated + [
            'used_count' => 0,
        ]);

        return redirect()->route('admin.coupons.edit', $coupon->id);
    }

    public function show(int $coupon): RedirectResponse
    {
        return redirect()->route('admin.coupons.edit', $coupon);
    }

    public function edit(int $coupon): View
    {
        $couponModel = Coupon::query()->findOrFail($coupon);

        return view('admin.coupons.form', [
            'title' => 'ویرایش کد تخفیف',
            'coupon' => $couponModel,
            'products' => $this->couponProductsList(),
            'selectedProductIds' => $couponModel->productIds(),
        ]);
    }

    public function update(Request $request, int $coupon): RedirectResponse
    {
        $couponModel = Coupon::query()->findOrFail($coupon);

        $validated = $this->validatePayload($request, $couponModel);

        $couponModel->forceFill($validated)->save();

        return redirect()->route('admin.coupons.edit', $couponModel->id);
    }

    public function destroy(int $coupon): RedirectResponse
    {
        $couponModel = Coupon::query()->findOrFail($coupon);
        $couponModel->delete();

        return redirect()->route('admin.coupons.index');
    }

    private function validatePayload(Request $request, ?Coupon $coupon = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'min:5',
                'max:8',
                'regex:/^[A-Za-z0-9]{5,8}$/',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'discount_type' => ['required', 'string', Rule::in(['percent', 'amount'])],
            'discount_value' => [
                'required',
                'integer',
                'min:0',
                'max:2000000000',
                Rule::when($request->input('discount_type') === 'percent', ['max:100']),
            ],
            'starts_at' => ['nullable', 'string', 'max:32'],
            'ends_at' => ['nullable', 'string', 'max:32'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:2000000000'],
            'is_active' => ['nullable'],
            'first_purchase_only' => ['nullable'],
            'apply_all_products' => ['nullable'],
            'product_ids' => [
                Rule::when(! $request->boolean('apply_all_products'), ['required', 'array', 'min:1'], ['nullable', 'array']),
            ],
            'product_ids.*' => [
                Rule::when(! $request->boolean('apply_all_products'), ['required', 'integer', 'distinct', 'exists:products,id'], ['nullable', 'integer', 'distinct', 'exists:products,id']),
            ],
        ]);

        $meta = $coupon?->meta ?? [];

        if ($request->boolean('first_purchase_only')) {
            $meta['first_purchase_only'] = true;
        } else {
            unset($meta['first_purchase_only']);
        }

        if ($request->boolean('apply_all_products')) {
            unset($meta['product_ids']);
        } else {
            $productIds = collect($validated['product_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            $meta['product_ids'] = $productIds;
        }

        return [
            'code' => strtoupper(trim((string) $validated['code'])),
            'discount_type' => (string) $validated['discount_type'],
            'discount_value' => (int) $validated['discount_value'],
            'starts_at' => $this->parseDateTimeOrFail('starts_at', $validated['starts_at'] ?? null),
            'ends_at' => $this->parseDateTimeOrFail('ends_at', $validated['ends_at'] ?? null),
            'usage_limit' => ($validated['usage_limit'] ?? null) !== null && (string) $validated['usage_limit'] !== '' ? (int) $validated['usage_limit'] : null,
            'per_user_limit' => 1,
            'is_active' => $request->boolean('is_active'),
            'meta' => $meta,
        ];
    }

    private function couponProductsList()
    {
        return Product::query()
            ->orderByDesc('id')
            ->limit(1000)
            ->get(['id', 'title', 'type']);
    }

    private function usageStats(Coupon $coupon): array
    {
        $totalRedemptions = CouponRedemption::query()->where('coupon_id', $coupon->id)->count();
        $uniqueUsers = CouponRedemption::query()->where('coupon_id', $coupon->id)->distinct('user_id')->count('user_id');

        $totalDiscount = (int) CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->whereNotNull('order_id')
            ->join('orders', 'orders.id', '=', 'coupon_redemptions.order_id')
            ->sum(DB::raw('orders.discount_amount'));

        $lastRedeemedAt = CouponRedemption::query()
            ->where('coupon_id', $coupon->id)
            ->orderByDesc('redeemed_at')
            ->orderByDesc('id')
            ->first()?->redeemed_at;

        return [
            'total_redemptions' => $totalRedemptions,
            'unique_users' => $uniqueUsers,
            'total_discount_amount' => $totalDiscount,
            'last_redeemed_at' => $lastRedeemedAt,
        ];
    }
}
