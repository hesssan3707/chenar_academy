<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $cacheKey = 'content_cache.posts.index.v1.limit30';

        $postIds = Cache::rememberForever($cacheKey, function () {
            return Post::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->take(30)
                ->pluck('id')
                ->all();
        });

        $trackedKeys = Cache::get('content_cache_keys.posts', []);
        if (! in_array($cacheKey, $trackedKeys, true)) {
            $trackedKeys[] = $cacheKey;
            Cache::forever('content_cache_keys.posts', $trackedKeys);
        }

        $postsById = Post::query()
            ->whereIn('id', $postIds)
            ->get()
            ->keyBy('id');

        $posts = collect($postIds)
            ->map(fn (int $id) => $postsById->get($id))
            ->filter();

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
