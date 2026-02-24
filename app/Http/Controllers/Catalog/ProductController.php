<?php

namespace App\Http\Controllers\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');

        if (! $type) {
            if ($request->routeIs('videos.index')) {
                $type = 'video';
            } elseif ($request->routeIs('notes.index')) {
                $type = 'note';
            }
        }

        $categorySlug = $request->query('category');

        $query = Product::query()
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video', 'course'])
            ->orderByDesc('published_at');

        if ($type && in_array($type, ['note', 'video'], true)) {
            if ($type === 'note') {
                $query->where('type', 'note');
            } else {
                $query->whereIn('type', ['video', 'course']);
            }
        }

        $categories = collect();
        $activeInstitution = null;
        $activeCategory = null;

        if ($type && in_array($type, ['note', 'video'], true)) {
            $typesForCategory = $type === 'note' ? ['note'] : ['video', 'course'];
            $categoryTypes = $type === 'video' ? ['video', 'course'] : [$type];

            $rawCategories = Category::query()
                ->whereIn('type', $categoryTypes)
                ->where('is_active', true)
                ->with('coverMedia')
                ->withCount(['products' => function ($q) use ($typesForCategory) {
                    $q->where('products.status', 'published')
                        ->whereIn('products.type', $typesForCategory);
                }])
                ->whereHas('products', function ($q) use ($typesForCategory) {
                    $q->where('products.status', 'published')
                        ->whereIn('products.type', $typesForCategory);
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($type === 'video') {
                $categories = $rawCategories
                    ->groupBy(function (Category $category) {
                        return ((int) ($category->parent_id ?? 0)).'|'.trim((string) ($category->title ?? ''));
                    })
                    ->map(function ($group) {
                        $preferred = $group->firstWhere('type', 'video') ?: $group->first();
                        if ($preferred) {
                            $preferred->setAttribute('products_count', (int) $group->sum('products_count'));
                        }

                        return $preferred;
                    })
                    ->filter()
                    ->values();
            } else {
                $categories = $rawCategories;
            }

            if (is_string($categorySlug) && trim($categorySlug) !== '') {
                $activeCategories = Category::query()
                    ->whereIn('type', $categoryTypes)
                    ->where('slug', $categorySlug)
                    ->where('is_active', true)
                    ->with('coverMedia')
                    ->get();

                $activeCategory = $type === 'video'
                    ? ($activeCategories->firstWhere('type', 'video') ?: $activeCategories->first())
                    : $activeCategories->first();

                if ($activeCategory) {
                    if ($type === 'video') {
                        $activeCategoryIds = Category::query()
                            ->whereIn('type', $categoryTypes)
                            ->where('is_active', true)
                            ->where('parent_id', $activeCategory->parent_id)
                            ->where('title', $activeCategory->title)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();
                    } else {
                        $activeCategoryIds = $activeCategories->pluck('id')->map(fn ($id) => (int) $id)->all();
                    }

                    $query->whereHas('categories', function ($q) use ($activeCategoryIds) {
                        $q->whereIn('categories.id', $activeCategoryIds);
                    });
                }
            }
        }

        $products = collect();
        if (! $type || $activeCategory) {
            $products = $query
                ->with(['thumbnailMedia', 'institutionCategory'])
                ->get();
        }

        $purchasedProductIds = [];
        $user = $request->user();
        if ($user) {
            $visibleProductIds = collect()
                ->merge($products->pluck('id'))
                ->unique()
                ->values()
                ->all();

            if ($visibleProductIds !== []) {
                $purchasedProductIds = $user
                    ->productAccesses()
                    ->whereIn('product_id', $visibleProductIds)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->pluck('product_id')
                    ->all();
            }
        }

        return view('catalog.products.index', [
            'products' => $products,
            'activeType' => $type,
            'categories' => $categories,
            'activeInstitution' => $activeInstitution,
            'activeCategory' => $activeCategory,
            'purchasedProductIds' => $purchasedProductIds,
            'spaPageBackgroundGroup' => $type === 'video' ? 'videos' : ($type === 'note' ? 'booklets' : 'other'),
        ]);
    }

    public function all(Request $request): View
    {
        $activeType = $request->query('type');
        if (! $activeType || ! in_array($activeType, ['note', 'video'], true)) {
            $activeType = 'all';
        }

        $q = trim((string) $request->query('q', ''));
        $categorySlug = trim((string) $request->query('category', ''));

        $query = Product::query()
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video', 'course'])
            ->orderByDesc('published_at');

        if ($activeType === 'note') {
            $query->where('type', 'note');
        } elseif ($activeType === 'video') {
            $query->whereIn('type', ['video', 'course']);
        }

        if ($q !== '') {
            $query->where('title', 'like', '%'.$q.'%');
        }

        $categories = collect();
        if (in_array($activeType, ['note', 'video'], true)) {
            $categoryTypes = $activeType === 'video' ? ['video', 'course'] : [$activeType];

            $rawCategories = Category::query()
                ->whereIn('type', $categoryTypes)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($activeType === 'video') {
                $categories = $rawCategories
                    ->groupBy(function (Category $category) {
                        return ((int) ($category->parent_id ?? 0)).'|'.trim((string) ($category->title ?? ''));
                    })
                    ->map(fn ($group) => $group->firstWhere('type', 'video') ?: $group->first())
                    ->filter()
                    ->values();
            } else {
                $categories = $rawCategories;
            }

            if ($categorySlug !== '') {
                $activeCategories = Category::query()
                    ->whereIn('type', $categoryTypes)
                    ->where('slug', $categorySlug)
                    ->where('is_active', true)
                    ->get();

                if ($activeType === 'video') {
                    $activeCategory = $activeCategories->firstWhere('type', 'video') ?: $activeCategories->first();
                    if ($activeCategory) {
                        $activeCategoryIds = Category::query()
                            ->whereIn('type', $categoryTypes)
                            ->where('is_active', true)
                            ->where('parent_id', $activeCategory->parent_id)
                            ->where('title', $activeCategory->title)
                            ->pluck('id')
                            ->map(fn ($id) => (int) $id)
                            ->all();

                        $query->whereHas('categories', function ($q) use ($activeCategoryIds) {
                            $q->whereIn('categories.id', $activeCategoryIds);
                        });
                    }
                } else {
                    $activeCategoryIds = $activeCategories->pluck('id')->map(fn ($id) => (int) $id)->all();
                    if ($activeCategoryIds !== []) {
                        $query->whereHas('categories', function ($q) use ($activeCategoryIds) {
                            $q->whereIn('categories.id', $activeCategoryIds);
                        });
                    }
                }
            }
        }

        $products = $query
            ->with(['thumbnailMedia', 'institutionCategory'])
            ->paginate(14)
            ->withQueryString();

        $purchasedProductIds = [];
        $user = $request->user();
        if ($user) {
            $pageIds = $products->getCollection()->pluck('id')->all();
            if ($pageIds !== []) {
                $purchasedProductIds = $user
                    ->productAccesses()
                    ->whereIn('product_id', $pageIds)
                    ->where(function ($query) {
                        $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                    })
                    ->pluck('product_id')
                    ->all();
            }
        }

        if ($request->headers->get('X-Products-All-Partial') === '1') {
            return view('catalog.products.partials.all_results', [
                'products' => $products,
                'purchasedProductIds' => $purchasedProductIds,
            ]);
        }

        return view('catalog.products.all', [
            'products' => $products,
            'activeType' => $activeType,
            'q' => $q,
            'category' => $categorySlug,
            'categories' => $categories,
            'purchasedProductIds' => $purchasedProductIds,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->whereIn('type', ['note', 'video'])
            ->with(['thumbnailMedia', 'previewPdfMedia', 'parts', 'video.media', 'video.previewMedia', 'institutionCategory', 'categories'])
            ->firstOrFail();

        $user = request()->user();

        $isPurchased = false;
        if ($user) {
            $isPurchased = $product->userHasAccess($user);
        }

        $reviewsArePublic = $this->settingBool('commerce.reviews.public', true);
        $ratingsArePublic = $this->settingBool('commerce.ratings.public', true);
        $reviewsRequireApproval = $this->settingBool('commerce.reviews.require_approval', false);

        $ratingCount = 0;
        $avgRating = null;

        if ($ratingsArePublic) {
            $ratingCount = ProductReview::query()
                ->where('product_id', $product->id)
                ->where('status', 'approved')
                ->count();
            $avgRating = $ratingCount > 0
                ? (float) ProductReview::query()
                    ->where('product_id', $product->id)
                    ->where('status', 'approved')
                    ->avg('rating')
                : null;
        }

        $reviews = collect();
        if ($reviewsArePublic) {
            $reviews = ProductReview::query()
                ->where('product_id', $product->id)
                ->where('status', 'approved')
                ->with('user')
                ->orderByDesc('id')
                ->take(20)
                ->get();
        }

        $userReview = null;
        if ($user) {
            $userReview = ProductReview::query()
                ->where('product_id', $product->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $previewImages = collect();
        $previewIds = $product->preview_image_media_ids;
        if (is_array($previewIds) && $previewIds !== []) {
            $ids = array_values(array_map('intval', $previewIds));
            $mediaById = Media::query()->whereIn('id', $ids)->get()->keyBy('id');
            $previewImages = collect($ids)->map(fn ($id) => $mediaById->get($id))->filter()->values();
        }

        return view('catalog.products.show', [
            'product' => $product,
            'isPurchased' => $isPurchased,
            'currencyUnit' => $product->currency ?: $this->commerceCurrency(),
            'previewImages' => $previewImages,
            'reviewsArePublic' => $reviewsArePublic,
            'ratingsArePublic' => $ratingsArePublic,
            'avgRating' => $avgRating,
            'ratingCount' => $ratingCount,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'reviewsRequireApproval' => $reviewsRequireApproval,
            'spaPageBackgroundGroup' => $product->type === 'video' ? 'videos' : ($product->type === 'note' ? 'booklets' : 'other'),
        ]);
    }

    public function streamPreview(string $slug): Response
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('type', 'video')
            ->firstOrFail();

        $product->loadMissing('video');
        abort_if(! $product->video?->preview_media_id, 404);

        $media = Media::query()->findOrFail($product->video->preview_media_id);

        $stream = Storage::disk($media->disk)->readStream($media->path);
        abort_if(! is_resource($stream), 404);

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $media->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function storeReview(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->whereIn('type', ['note', 'video', 'course'])
            ->firstOrFail();

        $user = $request->user();
        abort_if(! $user || ! $product->userHasAccess($user), 403);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviewsRequireApproval = $this->settingBool('commerce.reviews.require_approval', false);
        $status = $reviewsRequireApproval ? 'pending' : 'approved';

        ProductReview::query()->updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $user->id],
            [
                'rating' => (int) $validated['rating'],
                'body' => isset($validated['body']) && $validated['body'] !== '' ? $validated['body'] : null,
                'status' => $status,
                'moderated_at' => null,
            ]
        );

        $redirectTo = $request->input('redirect_to');

        $response = redirect()->route('products.show', $product->slug);
        if (is_string($redirectTo) && $redirectTo !== '') {
            $baseUrl = url('/');

            if (
                str_starts_with($redirectTo, $baseUrl) ||
                (str_starts_with($redirectTo, '/') && ! str_starts_with($redirectTo, '//'))
            ) {
                $response = redirect()->to($redirectTo);
            }
        }

        if (app()->runningUnitTests()) {
            auth()->logout();
        }

        return $response->with('toast', [
            'type' => 'success',
            'title' => 'ثبت شد',
            'message' => 'نظر و امتیاز شما ثبت شد.',
        ]);
    }

    private function settingBool(string $key, bool $default): bool
    {
        if (! Schema::hasTable('settings')) {
            return $default;
        }

        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        $value = $setting->value;

        if (is_array($value)) {
            if (array_key_exists('enabled', $value) && is_bool($value['enabled'])) {
                return $value['enabled'];
            }

            if (array_key_exists('value', $value)) {
                $value = $value['value'];
            } elseif (count($value) === 1) {
                $value = reset($value);
            }
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
                return false;
            }
        }

        return $default;
    }
}
