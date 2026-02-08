<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
                'currency' => 'IRR',
                'base_price' => 0,
            ]),
            'course' => new Course([
                'level' => null,
                'total_duration_seconds' => null,
                'meta' => [],
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        $product = Product::query()->create([
            'type' => 'course',
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'description' => $validated['description'],
            'thumbnail_media_id' => null,
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
            'meta' => [],
        ]);

        Course::query()->create([
            'product_id' => $product->id,
            'body' => $validated['body'],
            'level' => $validated['level'],
            'total_duration_seconds' => $validated['total_duration_seconds'],
            'meta' => [],
        ]);

        return redirect()->route('admin.courses.edit', $product->id);
    }

    public function show(int $course): View
    {
        return redirect()->route('admin.courses.edit', $course);
    }

    public function edit(int $course): View
    {
        $product = Product::query()
            ->where('type', 'course')
            ->findOrFail($course);

        $courseModel = Course::query()->find($product->id) ?? new Course([
            'product_id' => $product->id,
            'meta' => [],
        ]);

        return view('admin.courses.form', [
            'title' => 'ویرایش دوره',
            'courseProduct' => $product,
            'course' => $courseModel,
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
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'description' => $validated['description'],
            'status' => $validated['status'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'],
            'currency' => $validated['currency'],
            'published_at' => $validated['published_at'],
        ])->save();

        $courseRow = Course::query()->find($product->id);
        if (! $courseRow) {
            $courseRow = new Course(['product_id' => $product->id]);
        }

        $courseRow->forceFill([
            'body' => $validated['body'],
            'level' => $validated['level'],
            'total_duration_seconds' => $validated['total_duration_seconds'],
            'meta' => $courseRow->meta ?? [],
        ])->save();

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
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique('products', 'slug')->ignore($product?->id),
            ],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'base_price' => ['required', 'integer', 'min:0', 'max:2000000000'],
            'sale_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'currency' => ['required', 'string', 'size:3'],
            'published_at' => ['nullable', 'string', 'max:32'],
            'body' => ['nullable', 'string'],
            'level' => ['nullable', 'string', 'max:50'],
            'total_duration_seconds' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
        ]);

        $slug = Str::slug((string) ($validated['slug'] ?? ''), '-');

        return [
            'title' => (string) $validated['title'],
            'slug' => $slug !== '' ? $slug : Str::slug((string) $validated['title'], '-'),
            'excerpt' => isset($validated['excerpt']) && $validated['excerpt'] !== '' ? (string) $validated['excerpt'] : null,
            'description' => isset($validated['description']) && $validated['description'] !== '' ? (string) $validated['description'] : null,
            'status' => (string) $validated['status'],
            'base_price' => (int) $validated['base_price'],
            'sale_price' => ($validated['sale_price'] ?? null) !== null && (string) $validated['sale_price'] !== '' ? (int) $validated['sale_price'] : null,
            'currency' => strtoupper((string) $validated['currency']),
            'published_at' => $this->parseDateTimeOrFail('published_at', $validated['published_at'] ?? null),
            'body' => isset($validated['body']) && $validated['body'] !== '' ? (string) $validated['body'] : null,
            'level' => isset($validated['level']) && $validated['level'] !== '' ? (string) $validated['level'] : null,
            'total_duration_seconds' => ($validated['total_duration_seconds'] ?? null) !== null && (string) $validated['total_duration_seconds'] !== '' ? (int) $validated['total_duration_seconds'] : null,
        ];
    }
}
