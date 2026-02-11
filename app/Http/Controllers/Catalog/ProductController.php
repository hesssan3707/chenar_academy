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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');

        // Auto-detect type based on route name if not provided in query
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
            } elseif ($type === 'video') {
                $query->whereIn('type', ['video', 'course']);
            }
        }

        $courses = collect();
        if ($type === 'video') {
            $courses = Product::query()
                ->where('status', 'published')
                ->where('type', 'course')
                ->orderByDesc('published_at')
                ->with('thumbnailMedia')
                ->get();
        }

        $categories = collect();
        $activeInstitution = null;
        $activeCategory = null;

        if ($type && in_array($type, ['note', 'video'], true)) {
            $typesForCategory = $type === 'note' ? ['note'] : ['video', 'course'];

            $categories = Category::query()
                ->where('type', $type)
                ->where('is_active', true)
                ->whereHas('products', function ($q) use ($typesForCategory) {
                    $q->where('products.status', 'published')
                        ->whereIn('products.type', $typesForCategory);
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($categorySlug) {
                $activeCategory = Category::query()
                    ->where('type', $type)
                    ->where('slug', $categorySlug)
                    ->where('is_active', true)
                    ->first();

                if ($activeCategory) {
                    $query->whereHas('categories', function ($q) use ($activeCategory) {
                        $q->where('categories.id', $activeCategory->id);
                    });
                }
            }
        }

        if ($type && in_array($type, ['note', 'video'], true) && ! $activeCategory) {
            $latestProducts = collect();
            if ($type === 'video') {
                $latestProducts = Product::query()
                    ->where('status', 'published')
                    ->where('type', 'video')
                    ->orderByDesc('published_at')
                    ->with('thumbnailMedia')
                    ->limit(10)
                    ->get();
            } elseif ($type === 'note') {
                $latestProducts = Product::query()
                    ->where('status', 'published')
                    ->where('type', 'note')
                    ->orderByDesc('published_at')
                    ->with('thumbnailMedia')
                    ->limit(10)
                    ->get();
            }

            $purchasedProductIds = auth()->check() ? auth()->user()->purchasedProducts()->pluck('products.id')->toArray() : [];

            return view('catalog.products.index', [
                'products' => $latestProducts,
                'courses' => $courses,
                'activeType' => $type,
                'categories' => $categories,
                'activeInstitution' => $activeInstitution,
                'activeCategory' => $activeCategory,
                'purchasedProductIds' => $purchasedProductIds,
            ]);
        }

        $cacheKey = 'content_cache.products.index.v1.'.sha1(json_encode([
            'type' => $type,
            'category' => $categorySlug,
        ], JSON_THROW_ON_ERROR));

        $productIds = Cache::rememberForever($cacheKey, function () use ($query) {
            return (clone $query)->pluck('id')->all();
        });

        $trackedKeys = Cache::get('content_cache_keys.products', []);
        if (! in_array($cacheKey, $trackedKeys, true)) {
            $trackedKeys[] = $cacheKey;
            Cache::forever('content_cache_keys.products', $trackedKeys);
        }

        $productsById = Product::query()
            ->whereIn('id', $productIds)
            ->with('thumbnailMedia')
            ->get()
            ->keyBy('id');

        $products = collect($productIds)
            ->map(fn (int $id) => $productsById->get($id))
            ->filter();

        $purchasedProductIds = [];
        if ($request->user()) {
            $purchasedProductIds = $request->user()
                ->productAccesses()
                ->whereIn('product_id', $products->pluck('id')->all())
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->pluck('product_id')
                ->all();
        }

        return view('catalog.products.index', [
            'products' => $products,
            'courses' => $courses,
            'activeType' => $type,
            'categories' => $categories,
            'activeInstitution' => $activeInstitution,
            'activeCategory' => $activeCategory,
            'purchasedProductIds' => $purchasedProductIds,
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->whereIn('type', ['note', 'video'])
            ->with(['thumbnailMedia', 'parts', 'video.media', 'video.previewMedia'])
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

        return view('catalog.products.show', [
            'product' => $product,
            'isPurchased' => $isPurchased,
            'reviewsArePublic' => $reviewsArePublic,
            'ratingsArePublic' => $ratingsArePublic,
            'avgRating' => $avgRating,
            'ratingCount' => $ratingCount,
            'reviews' => $reviews,
            'userReview' => $userReview,
            'reviewsRequireApproval' => $reviewsRequireApproval,
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

        return Storage::disk($media->disk)->response($media->path, null, [
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

        if (is_array($value) && array_key_exists('enabled', $value) && is_bool($value['enabled'])) {
            return $value['enabled'];
        }

        return $default;
    }
}
