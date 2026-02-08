<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Product::query()
            ->where('status', 'published')
            ->where('type', 'course')
            ->orderByDesc('published_at')
            ->with('thumbnailMedia')
            ->get();

        return view('catalog.courses.index', ['courses' => $courses]);
    }

    public function show(string $slug): View
    {
        $course = Product::query()
            ->where('slug', $slug)
            ->where('type', 'course')
            ->with('thumbnailMedia')
            ->firstOrFail();

        return view('catalog.courses.show', ['course' => $course]);
    }
}
