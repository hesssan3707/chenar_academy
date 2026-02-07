<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'دسترسی‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد دسترسی']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.permissions.index');
    }

    public function show(int $permission): View
    {
        return view('admin.stub', ['title' => 'نمایش دسترسی']);
    }

    public function edit(int $permission): View
    {
        return view('admin.stub', ['title' => 'ویرایش دسترسی']);
    }

    public function update(Request $request, int $permission): RedirectResponse
    {
        return redirect()->route('admin.permissions.index');
    }

    public function destroy(int $permission): RedirectResponse
    {
        return redirect()->route('admin.permissions.index');
    }
}
