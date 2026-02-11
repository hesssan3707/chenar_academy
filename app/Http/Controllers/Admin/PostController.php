<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

        $coverMediaId = null;
        if (($validated['cover_image'] ?? null) instanceof UploadedFile) {
            $media = $this->storeUploadedMedia($validated['cover_image'], 'public', 'media/post-covers');
            $coverMediaId = $media->id;
        }

        $post = Post::query()->create([
            'author_user_id' => $request->user()?->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'],
            'cover_media_id' => $coverMediaId,
            'meta' => [],
        ]);

        return redirect()->route('admin.posts.edit', $post->id);
    }

    public function show(Post $post): RedirectResponse
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

        $coverMediaId = $post->cover_media_id;
        if (($validated['cover_image'] ?? null) instanceof UploadedFile) {
            $media = $this->storeUploadedMedia($validated['cover_image'], 'public', 'media/post-covers');
            $coverMediaId = $media->id;
        }

        $post->forceFill([
            'title' => $validated['title'],
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $validated['status'],
            'published_at' => $validated['published_at'],
            'cover_media_id' => $coverMediaId,
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
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:200000'],
            'status' => ['required', 'string', Rule::in($statusValues)],
            'published_at' => ['nullable', 'string', 'max:32'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
        ];

        $validated = $request->validate($rules);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'post-'.now()->format('YmdHis');
        }
        $slug = $post?->slug ?: $this->uniquePostSlug($baseSlug);

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'body' => (string) $validated['body'],
            'status' => (string) $validated['status'],
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'cover_image' => $validated['cover_image'] ?? null,
        ];
    }

    private function uniquePostSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): Media
    {
        $path = $file->store($directory, $disk);
        $path = str_replace('\\', '/', (string) $path);

        $width = null;
        $height = null;

        $mime = (string) ($file->getMimeType() ?: '');
        if ($mime !== '' && str_starts_with(strtolower($mime), 'image/')) {
            $imageSize = @getimagesize($file->getPathname());
            if (is_array($imageSize) && isset($imageSize[0], $imageSize[1])) {
                $width = is_numeric($imageSize[0]) ? (int) $imageSize[0] : null;
                $height = is_numeric($imageSize[1]) ? (int) $imageSize[1] : null;
            }
        }

        $sha1 = null;
        try {
            $sha1 = sha1_file($file->getPathname()) ?: null;
        } catch (\Throwable) {
            $sha1 = null;
        }

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => $sha1,
            'width' => $width,
            'height' => $height,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }
}
