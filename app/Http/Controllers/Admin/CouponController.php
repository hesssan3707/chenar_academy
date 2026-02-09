<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::query()->orderByDesc('id')->paginate(40);

        return view('admin.coupons.index', [
            'title' => 'کدهای تخفیف',
            'coupons' => $coupons,
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
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $coupon = Coupon::query()->create($validated + [
            'used_count' => 0,
            'meta' => [],
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
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'discount_type' => ['required', 'string', Rule::in(['percent', 'amount'])],
            'discount_value' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'starts_at' => ['nullable', 'string', 'max:32'],
            'ends_at' => ['nullable', 'string', 'max:32'],
            'usage_limit' => ['nullable', 'integer', 'min:1', 'max:2000000000'],
            'per_user_limit' => ['nullable', 'integer', 'min:1', 'max:2000000000'],
            'is_active' => ['nullable'],
        ]);

        return [
            'code' => strtoupper(trim((string) $validated['code'])),
            'discount_type' => (string) $validated['discount_type'],
            'discount_value' => (int) $validated['discount_value'],
            'starts_at' => $this->parseDateTimeOrFail('starts_at', $validated['starts_at'] ?? null),
            'ends_at' => $this->parseDateTimeOrFail('ends_at', $validated['ends_at'] ?? null),
            'usage_limit' => ($validated['usage_limit'] ?? null) !== null && (string) $validated['usage_limit'] !== '' ? (int) $validated['usage_limit'] : null,
            'per_user_limit' => ($validated['per_user_limit'] ?? null) !== null && (string) $validated['per_user_limit'] !== '' ? (int) $validated['per_user_limit'] : null,
            'is_active' => $request->boolean('is_active'),
            'meta' => $coupon?->meta ?? [],
        ];
    }
}
