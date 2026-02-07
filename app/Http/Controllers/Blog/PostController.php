<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->take(30)
            ->get();

        return view('blog.posts.index', [
            'posts' => $posts,
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        $blocks = $post->blocks()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('blog.posts.show', [
            'post' => $post,
            'blocks' => $blocks,
        ]);
    }
}
