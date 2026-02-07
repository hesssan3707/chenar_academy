<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'نقش‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد نقش']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.roles.index');
    }

    public function show(int $role): View
    {
        return view('admin.stub', ['title' => 'نمایش نقش']);
    }

    public function edit(int $role): View
    {
        return view('admin.stub', ['title' => 'ویرایش نقش']);
    }

    public function update(Request $request, int $role): RedirectResponse
    {
        return redirect()->route('admin.roles.index');
    }

    public function destroy(int $role): RedirectResponse
    {
        return redirect()->route('admin.roles.index');
    }
}
