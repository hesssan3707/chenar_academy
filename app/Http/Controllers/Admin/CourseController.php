<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        return view('admin.stub', ['title' => 'دوره‌ها']);
    }

    public function create(): View
    {
        return view('admin.stub', ['title' => 'ایجاد دوره']);
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.courses.index');
    }

    public function show(int $course): View
    {
        return view('admin.stub', ['title' => 'نمایش دوره']);
    }

    public function edit(int $course): View
    {
        return view('admin.stub', ['title' => 'ویرایش دوره']);
    }

    public function update(Request $request, int $course): RedirectResponse
    {
        return redirect()->route('admin.courses.index');
    }

    public function destroy(int $course): RedirectResponse
    {
        return redirect()->route('admin.courses.index');
    }
}
