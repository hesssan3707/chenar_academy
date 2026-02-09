<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CourseLesson;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductPart;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $accesses = $request->user()
            ->productAccesses()
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('granted_at')
            ->get();

        $products = Product::query()
            ->whereIn('id', $accesses->pluck('product_id')->all())
            ->orderByDesc('published_at')
            ->with('thumbnailMedia')
            ->get()
            ->keyBy('id');

        $items = $accesses
            ->map(function ($access) use ($products) {
                return [
                    'access' => $access,
                    'product' => $products->get($access->product_id),
                ];
            })
            ->filter(fn ($row) => $row['product'] !== null)
            ->values();

        return view('panel.library.index', [
            'title' => 'کتابخانه من',
            'noteItems' => $items->filter(fn ($row) => ($row['product']->type ?? null) === 'note')->values(),
            'videoItems' => $items->filter(function ($row) {
                $type = $row['product']->type ?? null;

                return $type === 'video' || $type === 'course';
            })->values(),
        ]);
    }

    public function show(Request $request, Product $product): View
    {
        abort_if(! $product->userHasAccess($request->user()), 403);

        if ($product->type === 'course') {
            $product->load(['thumbnailMedia', 'course.sections.lessons']);
        } else {
            $product->load(['thumbnailMedia', 'parts', 'video.media']);
        }

        $userReview = ProductReview::query()
            ->where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return view('panel.library.show', [
            'title' => $product->title,
            'product' => $product,
            'userReview' => $userReview,
        ]);
    }

    public function streamPart(Request $request, Product $product, ProductPart $part): Response
    {
        abort_if(! $product->userHasAccess($request->user()), 403);
        abort_if($part->product_id !== $product->id, 404);

        $mediaId = $part->media_id;
        abort_if(! $mediaId, 404);

        $media = Media::query()->findOrFail($mediaId);

        if ($product->type !== 'video') {
            $downloadName = $media->original_name ?: null;

            return Storage::disk($media->disk)->download($media->path, $downloadName, [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Cache-Control' => 'private, no-store, max-age=0',
                'Pragma' => 'no-cache',
            ]);
        }

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function streamLesson(Request $request, Product $product, CourseLesson $lesson): Response
    {
        abort_if(! $product->userHasAccess($request->user()), 403);
        abort_if($lesson->lesson_type !== 'video', 404);
        abort_if(! $lesson->media_id, 404);

        $lesson->loadMissing('section');
        abort_if(! $lesson->section || (int) $lesson->section->course_product_id !== (int) $product->id, 404);

        $media = Media::query()->findOrFail($lesson->media_id);

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function streamVideo(Request $request, Product $product): Response
    {
        abort_if(! $product->userHasAccess($request->user()), 403);
        abort_if($product->type !== 'video', 404);

        $product->loadMissing('video');
        abort_if(! $product->video?->media_id, 404);

        $media = Media::query()->findOrFail($product->video->media_id);

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
