<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'رسانه‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'آپلود رسانه']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }

    public function show(int $media): View
    {
        return view('admin.stub', ['title' => 'نمایش رسانه']);
    }

    public function edit(int $media): View
    {
        return view('admin.stub', ['title' => 'ویرایش رسانه']);
    }

    public function update(Request $request, int $media): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }

    public function destroy(int $media): RedirectResponse
    {
        return redirect()->route('admin.media.index');
    }
}
