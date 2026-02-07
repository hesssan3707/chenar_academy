<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'مقالات']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد مقاله']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.posts.index');
    }

    public function show(int $post): View
    {
        return view('admin.stub', ['title' => 'نمایش مقاله']);
    }

    public function edit(int $post): View
    {
        return view('admin.stub', ['title' => 'ویرایش مقاله']);
    }

    public function update(Request $request, int $post): RedirectResponse
    {
        return redirect()->route('admin.posts.index');
    }

    public function destroy(int $post): RedirectResponse
    {
        return redirect()->route('admin.posts.index');
    }
}
