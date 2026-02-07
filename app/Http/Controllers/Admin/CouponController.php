<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'کدهای تخفیف']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد کد تخفیف']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.coupons.index');
    }

    public function show(int $coupon): View
    {
        return view('admin.stub', ['title' => 'نمایش کد تخفیف']);
    }

    public function edit(int $coupon): View
    {
        return view('admin.stub', ['title' => 'ویرایش کد تخفیف']);
    }

    public function update(Request $request, int $coupon): RedirectResponse
    {
        return redirect()->route('admin.coupons.index');
    }

    public function destroy(int $coupon): RedirectResponse
    {
        return redirect()->route('admin.coupons.index');
    }
}
