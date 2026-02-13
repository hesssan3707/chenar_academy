<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Media;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featureItems = [
            [
                'title' => 'Final exam preparation',
                'description' => 'Step-by-step lessons and practice materials for end-of-term exams.',
            ],
            [
                'title' => 'MSc & PhD entrance exam prep',
                'description' => 'Curated content for competitive exams and university admissions.',
            ],
            [
                'title' => 'Specialized software training',
                'description' => 'Hands-on tutorials for essential academic and engineering tools.',
            ],
            [
                'title' => 'Educational webinars',
                'description' => 'Live sessions with recordings and supporting materials.',
            ],
        ];

        $specialOffersKey = 'content_cache.home.special_offers.v1.limit3';
        $latestProductsKey = 'content_cache.home.latest_products.v2.limit10';
        $latestPostsKey = 'content_cache.home.latest_posts.v1.limit3';
        $bestSellersKey = 'content_cache.home.best_sellers.v1.limit10';

        $specialOfferIds = Cache::rememberForever($specialOffersKey, function () {
            return Product::query()
                ->where('status', 'published')
                ->whereNotNull('sale_price')
                ->orderByDesc('published_at')
                ->take(3)
                ->pluck('id')
                ->all();
        });

        $latestProductIds = Cache::rememberForever($latestProductsKey, function () {
            return Product::query()
                ->where('status', 'published')
                ->whereIn('type', ['note', 'video', 'course'])
                ->orderByDesc('published_at')
                ->take(10)
                ->pluck('id')
                ->all();
        });

        $latestPostIds = Cache::rememberForever($latestPostsKey, function () {
            return Post::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->take(3)
                ->pluck('id')
                ->all();
        });

        $bestSellerProductIds = Cache::remember($bestSellersKey, 300, function () {
            return OrderItem::query()
                ->select('product_id')
                ->selectRaw('SUM(quantity) as total_qty')
                ->whereNotNull('product_id')
                ->whereHas('order', function ($query) {
                    $query->where('status', 'paid');
                })
                ->groupBy('product_id')
                ->orderByDesc('total_qty')
                ->orderByDesc('product_id')
                ->limit(10)
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        });

        $trackedKeys = Cache::get('content_cache_keys.home', []);
        foreach ([$specialOffersKey, $latestProductsKey, $latestPostsKey, $bestSellersKey] as $key) {
            if (! in_array($key, $trackedKeys, true)) {
                $trackedKeys[] = $key;
            }
        }
        Cache::forever('content_cache_keys.home', $trackedKeys);

        $specialOffersById = Product::query()->whereIn('id', $specialOfferIds)->with('thumbnailMedia')->get()->keyBy('id');
        $latestProductsById = Product::query()->whereIn('id', $latestProductIds)->with('thumbnailMedia')->get()->keyBy('id');
        $bestSellersById = Product::query()
            ->whereIn('id', $bestSellerProductIds)
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video', 'course'])
            ->with('thumbnailMedia')
            ->get()
            ->keyBy('id');
        $latestPostsById = Post::query()->whereIn('id', $latestPostIds)->get()->keyBy('id');

        $specialOffers = collect($specialOfferIds)->map(fn (int $id) => $specialOffersById->get($id))->filter();
        $latestProducts = collect($latestProductIds)->map(fn (int $id) => $latestProductsById->get($id))->filter();
        $bestSellers = collect($bestSellerProductIds)->map(fn (int $id) => $bestSellersById->get($id))->filter();
        $latestPosts = collect($latestPostIds)->map(fn (int $id) => $latestPostsById->get($id))->filter();

        $homeBanner = Banner::query()
            ->where('position', 'home')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        $purchasedProducts = collect();
        if (request()->user()) {
            $accesses = request()->user()
                ->productAccesses()
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->orderByDesc('granted_at')
                ->take(10)
                ->get();

            if ($accesses->isNotEmpty()) {
                $products = Product::query()
                    ->whereIn('id', $accesses->pluck('product_id')->all())
                    ->with('thumbnailMedia')
                    ->get()
                    ->keyBy('id');

                $purchasedProducts = $accesses
                    ->map(fn ($access) => $products->get($access->product_id))
                    ->filter();
            }
        }

        $homeBannerImageUrl = null;

        if ($homeBanner?->image_media_id) {
            $media = Media::query()->find($homeBanner->image_media_id);
            if ($media) {
                $homeBannerImageUrl = (string) call_user_func([Storage::disk((string) $media->disk), 'url'], $media->path);
            }
        }

        return view('home', [
            'featureItems' => $featureItems,
            'specialOffers' => $specialOffers,
            'latestProducts' => $latestProducts,
            'latestPosts' => $latestPosts,
            'homeBanner' => $homeBanner,
            'homeBannerImageUrl' => $homeBannerImageUrl,
            'purchasedProducts' => $purchasedProducts,
            'bestSellers' => $bestSellers,
        ]);
    }
}
