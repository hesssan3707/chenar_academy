<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Media;
use App\Models\Post;
use App\Models\Product;
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

        $specialOffers = Product::query()
            ->where('status', 'published')
            ->whereNotNull('sale_price')
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        $latestProducts = Product::query()
            ->where('status', 'published')
            ->whereIn('type', ['note', 'video'])
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

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

        $homeBannerImageUrl = null;

        if ($homeBanner?->image_media_id) {
            $media = Media::query()->find($homeBanner->image_media_id);
            if ($media) {
                $homeBannerImageUrl = Storage::disk($media->disk)->url($media->path);
            }
        }

        $latestPosts = Post::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->take(3)
            ->get();

        return view('home', [
            'featureItems' => $featureItems,
            'specialOffers' => $specialOffers,
            'latestProducts' => $latestProducts,
            'latestPosts' => $latestPosts,
            'homeBanner' => $homeBanner,
            'homeBannerImageUrl' => $homeBannerImageUrl,
        ]);
    }
}
