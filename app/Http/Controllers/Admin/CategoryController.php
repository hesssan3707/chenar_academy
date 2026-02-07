<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'دسته‌بندی‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد دسته‌بندی']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }

    public function show(int $category): View
    {
        return view('admin.stub', ['title' => 'نمایش دسته‌بندی']);
    }

    public function edit(int $category): View
    {
        return view('admin.stub', ['title' => 'ویرایش دسته‌بندی']);
    }

    public function update(Request $request, int $category): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }

    public function destroy(int $category): RedirectResponse
    {
        return redirect()->route('admin.categories.index');
    }
}
