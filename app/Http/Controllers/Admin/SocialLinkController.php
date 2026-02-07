<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'شبکه‌های اجتماعی']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد لینک شبکه اجتماعی']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.social-links.index');
    }

    public function show(int $social_link): View
    {
        return view('admin.stub', ['title' => 'نمایش لینک شبکه اجتماعی']);
    }

    public function edit(int $social_link): View
    {
        return view('admin.stub', ['title' => 'ویرایش لینک شبکه اجتماعی']);
    }

    public function update(Request $request, int $social_link): RedirectResponse
    {
        return redirect()->route('admin.social-links.index');
    }

    public function destroy(int $social_link): RedirectResponse
    {
        return redirect()->route('admin.social-links.index');
    }
}
