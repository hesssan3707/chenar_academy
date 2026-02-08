<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::query()->orderByDesc('published_at')->orderByDesc('id')->paginate(40);

        return view('admin.posts.index', [
            'title' => 'مقالات',
            'posts' => $posts,
        ]);
    }

    public function create(): View
    {
        $post = new Post([
            'status' => 'draft',
        ]);

        return view('admin.posts.form', [
            'title' => 'ایجاد مقاله',
            'post' => $post,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $post = Post::query()->create([
            'author_user_id' => $request->user()?->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'],
            'cover_media_id' => null,
            'meta' => [],
        ]);

        return redirect()->route('admin.posts.edit', $post->id);
    }

    public function show(Post $post): View
    {
        return redirect()->route('admin.posts.edit', $post->id);
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', [
            'title' => 'ویرایش مقاله',
            'post' => $post,
        ]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $validated = $this->validatePayload($request, $post);

        $post->forceFill([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'],
        ])->save();

        return redirect()->route('admin.posts.edit', $post->id);
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index');
    }

    private function validatePayload(Request $request, ?Post $post = null): array
    {
        $statusValues = ['draft', 'published'];

        $rules = [
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique('posts', 'slug')->ignore($post?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in($statusValues)],
            'published_at' => ['nullable', 'string', 'max:32'],
        ];

        $validated = $request->validate($rules);

        $slug = Str::slug((string) ($validated['slug'] ?? ''), '-');

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug !== '' ? $slug : Str::slug((string) $validated['title'], '-'),
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'status' => (string) $validated['status'],
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
        ];
    }
}
