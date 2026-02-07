<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'سفارش‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد سفارش']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.orders.index');
    }

    public function show(int $order): View
    {
        return view('admin.stub', ['title' => 'نمایش سفارش']);
    }

    public function edit(int $order): View
    {
        return view('admin.stub', ['title' => 'ویرایش سفارش']);
    }

    public function update(Request $request, int $order): RedirectResponse
    {
        return redirect()->route('admin.orders.index');
    }

    public function destroy(int $order): RedirectResponse
    {
        return redirect()->route('admin.orders.index');
    }
}
