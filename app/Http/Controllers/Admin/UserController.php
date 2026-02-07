<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index');
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد کاربر']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.users.index');
    }

    public function show(int $user): View
    {
        return view('admin.stub', ['title' => 'نمایش کاربر']);
    }

    public function edit(int $user): View
    {
        return view('admin.stub', ['title' => 'ویرایش کاربر']);
    }

    public function update(Request $request, int $user): RedirectResponse
    {
        return redirect()->route('admin.users.index');
    }

    public function destroy(int $user): RedirectResponse
    {
        return redirect()->route('admin.users.index');
    }
}
