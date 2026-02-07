<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'بنرها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد بنر']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.banners.index');
    }

    public function show(int $banner): View
    {
        return view('admin.stub', ['title' => 'نمایش بنر']);
    }

    public function edit(int $banner): View
    {
        return view('admin.stub', ['title' => 'ویرایش بنر']);
    }

    public function update(Request $request, int $banner): RedirectResponse
    {
        return redirect()->route('admin.banners.index');
    }

    public function destroy(int $banner): RedirectResponse
    {
        return redirect()->route('admin.banners.index');
    }
}
