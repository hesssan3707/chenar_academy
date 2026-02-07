<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'پرداخت‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد پرداخت']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.payments.index');
    }

    public function show(int $payment): View
    {
        return view('admin.stub', ['title' => 'نمایش پرداخت']);
    }

    public function edit(int $payment): View
    {
        return view('admin.stub', ['title' => 'ویرایش پرداخت']);
    }

    public function update(Request $request, int $payment): RedirectResponse
    {
        return redirect()->route('admin.payments.index');
    }

    public function destroy(int $payment): RedirectResponse
    {
        return redirect()->route('admin.payments.index');
    }
}
