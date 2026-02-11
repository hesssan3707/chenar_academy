<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\CourseLesson;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

    public function show(Request $request, string $slug): View
    {
        $course = Product::query()
            ->where('slug', $slug)
            ->where('type', 'course')
            ->with(['thumbnailMedia', 'course.sections.lessons'])
            ->firstOrFail();

        $isPurchased = false;
        if ($request->user()) {
            $isPurchased = $course->userHasAccess($request->user());
        }

        return view('catalog.courses.show', [
            'course' => $course,
            'isPurchased' => $isPurchased,
        ]);
    }

    public function streamPreviewLesson(Request $request, string $slug, CourseLesson $lesson): Response
    {
        $course = Product::query()
            ->where('slug', $slug)
            ->where('type', 'course')
            ->firstOrFail();

        abort_if(! $lesson->is_preview, 403);
        abort_if($lesson->lesson_type !== 'video', 404);
        abort_if(! $lesson->media_id, 404);

        $lesson->loadMissing('section');
        abort_if(! $lesson->section || (int) $lesson->section->course_product_id !== (int) $course->id, 404);

        $media = Media::query()->findOrFail($lesson->media_id);

        return Storage::disk($media->disk)->response($media->path, null, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
