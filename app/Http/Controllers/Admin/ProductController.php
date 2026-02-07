<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'محصولات']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد محصول']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.products.index');
    }

    public function show(int $product): View
    {
        return view('admin.stub', ['title' => 'نمایش محصول']);
    }

    public function edit(int $product): View
    {
        return view('admin.stub', ['title' => 'ویرایش محصول']);
    }

    public function update(Request $request, int $product): RedirectResponse
    {
        return redirect()->route('admin.products.index');
    }

    public function destroy(int $product): RedirectResponse
    {
        return redirect()->route('admin.products.index');
    }
}
