<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseSection;
use App\Models\Media;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Product::query()
            ->where('type', 'course')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(40);

        return view('admin.courses.index', [
            'title' => 'دوره‌ها',
            'courses' => $courses,
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.form', [
            'title' => 'ایجاد دوره',
            'courseProduct' => new Product([
                'type' => 'course',
                'status' => 'draft',
                'currency' => $this->commerceCurrency(),
                'base_price' => 0,
            ]),
            'course' => new Course([
                'total_duration_seconds' => null,
                'total_videos_count' => null,
                'meta' => [],
            ]),
            'institutions' => Category::query()
                ->ofType('institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->ofType('video')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $product = Product::query()->create([
            'type' => 'course',
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'excerpt' => null,
            'thumbnail_media_id' => $validated['thumbnail_media_id'],
            'institution_category_id' => $validated['institution_category_id'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        $product->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

        Course::query()->create([
            'product_id' => $product->id,
            'body' => null,
            'level' => null,
            'total_duration_seconds' => null,
            'total_videos_count' => null,
            'meta' => [],
        ]);

        $this->syncCourseLessons($request, $product);

        return redirect()->route('admin.courses.edit', $product->id);
    }

    public function show(int $course): RedirectResponse
    {
        return redirect()->route('admin.courses.edit', $course);
    }

    public function edit(int $course): View
    {
        $product = Product::query()
            ->where('type', 'course')
            ->with(['thumbnailMedia', 'course.sections.lessons'])
            ->findOrFail($course);

        $courseModel = Course::query()->find($product->id) ?? new Course([
            'product_id' => $product->id,
            'meta' => [],
        ]);

        return view('admin.courses.form', [
            'title' => 'ویرایش دوره',
            'courseProduct' => $product,
            'course' => $courseModel,
            'institutions' => Category::query()
                ->ofType('institution')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
            'categories' => Category::query()
                ->ofType('video')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('title')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function update(Request $request, int $course): RedirectResponse
    {
        $product = Product::query()
            ->where('type', 'course')
            ->findOrFail($course);

        $validated = $this->validatePayload($request, $product);

        $product->forceFill([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'excerpt' => null,
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'currency' => $this->commerceCurrency(),
            'published_at' => $validated['published_at'],
            'thumbnail_media_id' => $validated['remove_cover_image']
                ? null
                : ($validated['thumbnail_media_id'] ?? $product->thumbnail_media_id),
            'institution_category_id' => $validated['institution_category_id'],
        ])->save();

        $product->categories()->sync(($validated['category_id'] ?? null) !== null ? [(int) $validated['category_id']] : []);

        $courseRow = Course::query()->find($product->id);
        if (! $courseRow) {
            $courseRow = new Course(['product_id' => $product->id]);
        }

        $courseRow->forceFill([
            'body' => null,
            'level' => null,
            'meta' => $courseRow->meta ?? [],
        ])->save();

        $this->syncCourseLessons($request, $product);

        return redirect()->route('admin.courses.edit', $product->id);
    }

    public function destroy(int $course): RedirectResponse
    {
        $product = Product::query()
            ->where('type', 'course')
            ->findOrFail($course);

        $product->delete();

        return redirect()->route('admin.courses.index');
    }

    private function validatePayload(Request $request, ?Product $product = null): array
    {
        $intent = trim((string) $request->input('intent', ''));

        $inputStatus = trim((string) $request->input('status', ''));
        $shouldPublish = $intent === 'publish' || ($intent === '' && $inputStatus === 'published');

        $status = match ($intent) {
            'publish' => 'published',
            'draft' => 'draft',
            '' => ($inputStatus !== '' ? $inputStatus : (string) ($product?->status ?? 'draft')),
            default => (string) ($product?->status ?? 'draft'),
        };
        if ($status !== 'draft' && $status !== 'published') {
            $status = 'draft';
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'institution_category_id' => ['required', 'integer', 'min:1', Rule::exists('categories', 'id')->where('category_type_id', Category::typeId('institution'))],
            'category_id' => ['required', 'integer', 'min:1', Rule::exists('categories', 'id')->where('category_type_id', Category::typeId('video'))],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', Rule::in(['draft', 'published'])],
            'base_price' => [$shouldPublish ? 'required' : 'nullable', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000', 'prohibits:discount_type,discount_value'],
            'discount_type' => ['nullable', 'string', Rule::in(['percent', 'amount']), 'required_with:discount_value', 'prohibits:sale_price'],
            'discount_value' => [
                'nullable',
                'integer',
                'min:0',
                'max:2000000000',
                'required_with:discount_type',
                'prohibits:sale_price',
                Rule::when($request->input('discount_type') === 'percent', ['max:100']),
            ],
            'published_at' => ['nullable', 'string', 'max:32'],
            'cover_image' => ['nullable', 'file', 'image', 'max:5120'],
            'remove_cover_image' => ['nullable', 'in:0,1'],
            'lessons' => ['nullable', 'array'],
            'lessons.*.title' => ['nullable', 'string', 'max:180'],
            'lessons.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'lessons.*.is_preview' => ['nullable', 'in:0,1'],
            'lessons.*.delete' => ['nullable', 'in:0,1'],
            'lessons.*.remove_media' => ['nullable', 'in:0,1'],
            'lessons.*.video_url' => ['nullable', 'string', 'max:2048', 'url'],
            'lessons.*.file' => ['nullable', 'file', 'max:1048576'],
        ]);

        $baseSlug = Str::slug((string) $validated['title'], '-');
        if ($baseSlug === '') {
            $baseSlug = 'course-'.now()->format('YmdHis');
        }
        $slug = $product?->slug ?: $this->uniqueProductSlug($baseSlug, null);

        $thumbnailMedia = $this->storeUploadedMedia($request->file('cover_image'), 'public', 'uploads/covers');

        $publishedAt = $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null);
        if ($shouldPublish && $publishedAt === null) {
            $publishedAt = now(config('app.timezone'));
        }

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'status' => $status,
            'base_price' => (int) ($validated['base_price'] ?? ($product?->base_price ?? 0)),
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'discount_type' => isset($validated['discount_type']) && $validated['discount_type'] !== '' ? (string) $validated['discount_type'] : null,
            'discount_value' => ($validated['discount_value'] ?? null) !== null && (string) $validated['discount_value'] !== '' ? (int) $validated['discount_value'] : null,
            'published_at' => $status === 'published' ? $publishedAt : null,
            'thumbnail_media_id' => $thumbnailMedia?->id,
            'remove_cover_image' => $request->boolean('remove_cover_image'),
            'institution_category_id' => (int) $validated['institution_category_id'],
            'category_id' => (int) $validated['category_id'],
        ];
    }

    private function syncCourseLessons(Request $request, Product $product): void
    {
        $course = Course::query()->find($product->id);
        if (! $course) {
            $course = Course::query()->create([
                'product_id' => $product->id,
                'body' => null,
                'level' => null,
                'total_duration_seconds' => null,
                'total_videos_count' => null,
                'meta' => [],
            ]);
        }

        $section = CourseSection::query()
            ->where('course_product_id', $product->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();

        if (! $section) {
            $section = CourseSection::query()->create([
                'course_product_id' => $product->id,
                'title' => 'ویدیوها',
                'sort_order' => 0,
            ]);
        }

        $lessonsInput = $request->input('lessons', []);
        if (! is_array($lessonsInput)) {
            $lessonsInput = [];
        }
        if ($lessonsInput === []) {
            $payload = $request->all('lessons');
            if (isset($payload['lessons']) && is_array($payload['lessons'])) {
                $lessonsInput = $payload['lessons'];
            }
        }

        if ($request->hasFile('lessons')) {
            set_time_limit(0);
        }

        foreach ($lessonsInput as $key => $lessonRow) {
            if (! is_array($lessonRow)) {
                continue;
            }

            $title = trim((string) ($lessonRow['title'] ?? ''));
            $sortOrder = (int) ($lessonRow['sort_order'] ?? 0);
            $isPreview = (string) ($lessonRow['is_preview'] ?? '0') === '1';
            $delete = (string) ($lessonRow['delete'] ?? '0') === '1';
            $removeMedia = (string) ($lessonRow['remove_media'] ?? '0') === '1';
            $videoUrl = trim((string) ($lessonRow['video_url'] ?? ''));
            $videoUrl = $videoUrl !== '' ? $videoUrl : null;

            $uploadedFile = $request->file("lessons.$key.file");
            if ($uploadedFile !== null && ! ($uploadedFile instanceof UploadedFile)) {
                $uploadedFile = null;
            }
            if ($uploadedFile === null && isset($lessonRow['file']) && $lessonRow['file'] instanceof UploadedFile) {
                $uploadedFile = $lessonRow['file'];
            }

            if ($uploadedFile && $videoUrl) {
                throw ValidationException::withMessages([
                    "lessons.$key.video_url" => ['فقط یکی از فایل یا لینک را وارد کنید.'],
                ]);
            }

            $lessonId = isset($lessonRow['id']) && is_numeric($lessonRow['id']) ? (int) $lessonRow['id'] : null;
            $lesson = $lessonId ? CourseLesson::query()->find($lessonId) : null;

            if ($lesson) {
                $lesson->loadMissing('section');
                if (! $lesson->section || (int) $lesson->section->course_product_id !== (int) $product->id) {
                    continue;
                }

                if ($delete) {
                    $lesson->delete();

                    continue;
                }

                $payload = [
                    'title' => $title !== '' ? $title : $lesson->title,
                    'sort_order' => $sortOrder,
                    'is_preview' => $isPreview,
                ];

                $finalMediaId = $removeMedia ? null : ((int) ($lesson->media_id ?? 0) ?: null);
                $finalVideoUrl = array_key_exists('video_url', $lessonRow)
                    ? $videoUrl
                    : (trim((string) ($lesson->video_url ?? '')) !== '' ? trim((string) $lesson->video_url) : null);
                $finalDurationSeconds = $finalMediaId !== null ? (int) ($lesson->duration_seconds ?? 0) : null;

                if ($uploadedFile) {
                    $media = $this->storeUploadedMedia($uploadedFile, 'local', 'protected/course-lessons');
                    if ($media) {
                        $finalMediaId = $media->id;
                        $finalVideoUrl = null;
                        $finalDurationSeconds = $this->extractVideoDurationSecondsOrFail($media);
                    }
                } elseif ($finalVideoUrl !== null) {
                    $finalMediaId = null;
                    $finalDurationSeconds = null;
                }

                $hasMediaSource = $finalMediaId !== null;
                $hasUrlSource = $finalVideoUrl !== null;
                if (($hasMediaSource && $hasUrlSource) || (! $hasMediaSource && ! $hasUrlSource)) {
                    throw ValidationException::withMessages([
                        "lessons.$key.video_url" => ['Ø¨Ø±Ø§ÛŒ Ù‡Ø± Ø¯Ø±Ø³ Ø¨Ø§ÛŒØ¯ Ø¯Ù‚ÛŒÙ‚Ø§Ù‹ ÛŒÚ©ÛŒ Ø§Ø² Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÛŒØ§ ÙØ§ÛŒÙ„ ÙˆÛŒØ¯ÛŒÙˆ ØªØ¹ÛŒÛŒÙ† Ø´ÙˆØ¯.'],
                    ]);
                }

                $payload['media_id'] = $finalMediaId;
                $payload['video_url'] = $finalVideoUrl;
                $payload['lesson_type'] = 'video';
                $payload['duration_seconds'] = $hasMediaSource ? $finalDurationSeconds : null;

                $lesson->forceFill($payload)->save();

                continue;
            }

            if ($delete) {
                continue;
            }

            $hasAnyInput = $title !== '' || $uploadedFile !== null || $videoUrl !== null;
            if (! $hasAnyInput) {
                continue;
            }

            if ($uploadedFile && $videoUrl !== null) {
                throw ValidationException::withMessages([
                    "lessons.$key.video_url" => ['ÙÙ‚Ø· ÛŒÚ©ÛŒ Ø§Ø² ÙØ§ÛŒÙ„ ÛŒØ§ Ù„ÛŒÙ†Ú© Ø±Ø§ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯.'],
                ]);
            }

            if (! $uploadedFile && $videoUrl === null) {
                throw ValidationException::withMessages([
                    "lessons.$key.video_url" => ['Ø¨Ø§ÛŒØ¯ Ø¨Ø±Ø§ÛŒ Ø¯Ø±Ø³ Ø¬Ø¯ÛŒØ¯ ÛŒØ§ Ù„ÛŒÙ†Ú© ÙˆÛŒØ¯ÛŒÙˆ ÙˆØ§Ø±Ø¯ Ú©Ù†ÛŒØ¯ ÛŒØ§ ÙØ§ÛŒÙ„ Ø¢Ù¾Ù„ÙˆØ¯ Ú©Ù†ÛŒØ¯.'],
                ]);
            }

            if ($title === '') {
                throw ValidationException::withMessages([
                    "lessons.$key.title" => ['عنوان ویدیو الزامی است.'],
                ]);
            }

            $mediaId = null;
            $durationSeconds = null;
            if ($uploadedFile) {
                $media = $this->storeUploadedMedia($uploadedFile, 'local', 'protected/course-lessons');
                if ($media) {
                    $mediaId = $media->id;
                    $durationSeconds = $this->extractVideoDurationSecondsOrFail($media);
                }
            }

            CourseLesson::query()->create([
                'course_section_id' => $section->id,
                'title' => $title,
                'sort_order' => $sortOrder,
                'lesson_type' => 'video',
                'media_id' => $mediaId,
                'video_url' => $videoUrl,
                'content' => null,
                'is_preview' => $isPreview,
                'duration_seconds' => $durationSeconds,
                'meta' => [],
            ]);
        }

        $videoLessons = CourseLesson::query()
            ->whereIn('course_section_id', CourseSection::query()->where('course_product_id', $product->id)->pluck('id'))
            ->where('lesson_type', 'video')
            ->get(['duration_seconds']);

        $course->forceFill([
            'total_videos_count' => $videoLessons->count(),
            'total_duration_seconds' => $videoLessons->sum(fn ($row) => (int) ($row->duration_seconds ?? 0)),
        ])->save();
    }

    private function extractVideoDurationSecondsOrFail(Media $media): int
    {
        $seconds = $this->extractVideoDurationSeconds($media);
        if ($seconds === null) {
            throw ValidationException::withMessages([
                'lessons' => ['امکان استخراج مدت زمان ویدیو وجود ندارد.'],
            ]);
        }

        $media->forceFill(['duration_seconds' => $seconds])->save();

        return $seconds;
    }

    private function extractVideoDurationSeconds(Media $media): ?int
    {
        $absolutePath = $this->resolveLocalDiskPath($media->disk, $media->path);
        if ($absolutePath === null) {
            return null;
        }

        $result = Process::timeout(30)->run([
            'ffprobe',
            '-v',
            'error',
            '-show_entries',
            'format=duration',
            '-of',
            'default=noprint_wrappers=1:nokey=1',
            $absolutePath,
        ]);

        if (! $result->successful()) {
            return null;
        }

        $raw = trim($result->output());
        if ($raw === '' || ! is_numeric($raw)) {
            return null;
        }

        $seconds = (int) round((float) $raw);

        return $seconds >= 0 ? $seconds : null;
    }

    private function storeUploadedMedia(?UploadedFile $file, string $disk, string $directory): ?Media
    {
        if (! $file) {
            return null;
        }

        $path = $file->store($directory, $disk);

        return Media::query()->create([
            'uploaded_by_user_id' => request()->user()?->id,
            'disk' => $disk,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha1' => null,
            'width' => null,
            'height' => null,
            'duration_seconds' => null,
            'meta' => [],
        ]);
    }

    private function resolveLocalDiskPath(string $disk, string $path): ?string
    {
        $root = config("filesystems.disks.$disk.root");
        if (! is_string($root) || $root === '') {
            return null;
        }

        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));

        return rtrim($root, '/\\').DIRECTORY_SEPARATOR.$relativePath;
    }

    private function uniqueProductSlug(string $baseSlug, ?int $ignoreProductId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($ignoreProductId, fn ($q) => $q->where('id', '!=', $ignoreProductId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
