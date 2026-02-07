<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('blog.posts.index');
    }

    public function show(string $slug): View
    {
        return view('blog.posts.show', ['slug' => $slug]);
    }
}
