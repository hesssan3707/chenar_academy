<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\Product;
use App\Models\Video;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPostCategories();
        $this->seedTicketCategories();
        $this->seedInstitutionsAndCatalogCategories();
        $this->seedPosts();
        $this->seedNotes();
        $this->seedVideos();
        $this->seedCourses();
    }

    private function seedPostCategories(): void
    {
        $items = [
            ['title' => 'اخبار', 'slug' => 'news'],
            ['title' => 'مطالعه و یادگیری', 'slug' => 'learning'],
            ['title' => 'راهنما', 'slug' => 'guide'],
        ];

        foreach ($items as $item) {
            Category::query()->firstOrCreate(
                ['type' => 'post', 'slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'parent_id' => null,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }
    }

    private function seedTicketCategories(): void
    {
        $items = [
            ['title' => 'پشتیبانی فنی', 'slug' => 'technical'],
            ['title' => 'پرداخت و سفارش', 'slug' => 'billing'],
            ['title' => 'سایر', 'slug' => 'other'],
        ];

        foreach ($items as $item) {
            Category::query()->firstOrCreate(
                ['type' => 'ticket', 'slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'parent_id' => null,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }
    }

    private function seedInstitutionsAndCatalogCategories(): void
    {
        $institutions = [
            ['title' => 'دانشگاه آزاد اسلامی', 'slug' => 'iau', 'icon_key' => 'university'],
            ['title' => 'دانشگاه پیام نور', 'slug' => 'pnu', 'icon_key' => 'university'],
        ];

        foreach ($institutions as $institution) {
            Category::query()->firstOrCreate(
                ['type' => 'institution', 'slug' => $institution['slug']],
                [
                    'title' => $institution['title'],
                    'parent_id' => null,
                    'icon_key' => $institution['icon_key'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }

        $items = [
            ['type' => 'note', 'title' => 'ریاضی ۱', 'slug' => 'iau-math-1', 'parent' => 'iau', 'icon_key' => 'math'],
            ['type' => 'note', 'title' => 'ریاضی ۲', 'slug' => 'iau-math-2', 'parent' => 'iau', 'icon_key' => 'math'],
            ['type' => 'note', 'title' => 'فیزیک', 'slug' => 'iau-physics', 'parent' => 'iau', 'icon_key' => 'physics'],
            ['type' => 'note', 'title' => 'ریاضی ۱', 'slug' => 'pnu-math-1', 'parent' => 'pnu', 'icon_key' => 'math'],
            ['type' => 'note', 'title' => 'محاسبات', 'slug' => 'pnu-calculus', 'parent' => 'pnu', 'icon_key' => 'calculator'],
            ['type' => 'note', 'title' => 'فیزیک', 'slug' => 'pnu-physics', 'parent' => 'pnu', 'icon_key' => 'physics'],
            ['type' => 'video', 'title' => 'ریاضی', 'slug' => 'iau-video-math', 'parent' => 'iau', 'icon_key' => 'video'],
            ['type' => 'video', 'title' => 'فیزیک', 'slug' => 'iau-video-physics', 'parent' => 'iau', 'icon_key' => 'video'],
            ['type' => 'video', 'title' => 'ریاضی', 'slug' => 'pnu-video-math', 'parent' => 'pnu', 'icon_key' => 'video'],
            ['type' => 'video', 'title' => 'محاسبات', 'slug' => 'pnu-video-calculus', 'parent' => 'pnu', 'icon_key' => 'video'],
        ];

        foreach ($items as $item) {
            $institution = Category::query()
                ->where('type', 'institution')
                ->where('slug', $item['parent'])
                ->first();

            if (! $institution) {
                continue;
            }

            Category::query()->firstOrCreate(
                ['type' => $item['type'], 'slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'parent_id' => $institution->id,
                    'icon_key' => $item['icon_key'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }
    }

    private function seedPosts(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Post::query()->firstOrCreate(['slug' => 'post-'.$i], [
                'author_user_id' => null,
                'title' => 'مقاله نمونه شماره '.$i,
                'excerpt' => 'خلاصه کوتاه برای مقاله نمونه شماره '.$i,
                'status' => 'published',
                'published_at' => now()->subDays(5 - $i),
                'cover_media_id' => null,
                'meta' => [],
            ]);
        }
    }

    private function seedNotes(): void
    {
        $noteCategorySlugs = [
            1 => 'iau-math-1',
            2 => 'iau-math-2',
            3 => 'pnu-math-1',
            4 => 'pnu-calculus',
            5 => 'pnu-physics',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $product = Product::query()->firstOrCreate(['slug' => 'note-'.$i], [
                'type' => 'note',
                'title' => 'جزوه آموزشی شماره '.$i,
                'excerpt' => 'جزوه خلاصه و کاربردی برای مطالعه.',
                'description' => 'این جزوه به صورت نمونه برای مرحله فعلی پروژه ایجاد شده است.',
                'status' => 'published',
                'base_price' => 80000,
                'sale_price' => $i === 1 ? 65000 : null,
                'currency' => 'IRR',
                'published_at' => now()->subDays(10 - $i),
                'meta' => $i === 1 ? ['badge' => 'فروش ویژه'] : [],
            ]);

            $categorySlug = $noteCategorySlugs[$i] ?? null;
            if ($categorySlug) {
                $category = Category::query()->where('type', 'note')->where('slug', $categorySlug)->first();
                if ($category) {
                    $product->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        }
    }

    private function seedVideos(): void
    {
        $videoCategorySlugs = [
            1 => 'iau-video-math',
            2 => 'iau-video-physics',
            3 => 'pnu-video-math',
            4 => 'pnu-video-calculus',
            5 => 'pnu-video-math',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $product = Product::query()->firstOrCreate(['slug' => 'video-'.$i], [
                'type' => 'video',
                'title' => 'ویدیو آموزشی شماره '.$i,
                'excerpt' => 'ویدیو آموزشی برای تمرین و یادگیری.',
                'description' => 'این ویدیو به صورت نمونه برای مرحله فعلی پروژه ایجاد شده است.',
                'status' => 'published',
                'base_price' => 90000,
                'sale_price' => $i === 1 ? 70000 : null,
                'currency' => 'IRR',
                'published_at' => now()->subDays(20 - $i),
                'meta' => $i === 1 ? ['badge' => 'فروش ویژه'] : [],
            ]);

            Video::query()->firstOrCreate(['product_id' => $product->id], [
                'media_id' => null,
                'duration_seconds' => 1800,
                'meta' => [],
            ]);

            $categorySlug = $videoCategorySlugs[$i] ?? null;
            if ($categorySlug) {
                $category = Category::query()->where('type', 'video')->where('slug', $categorySlug)->first();
                if ($category) {
                    $product->categories()->syncWithoutDetaching([$category->id]);
                }
            }
        }
    }

    private function seedCourses(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $product = Product::query()->firstOrCreate(['slug' => 'course-'.$i], [
                'type' => 'course',
                'title' => 'دوره آموزشی شماره '.$i,
                'excerpt' => 'دوره جامع برای یادگیری مرحله‌به‌مرحله.',
                'description' => 'این دوره به صورت نمونه برای مرحله فعلی پروژه ایجاد شده است.',
                'status' => 'published',
                'base_price' => 120000,
                'sale_price' => $i === 1 ? 95000 : null,
                'currency' => 'IRR',
                'published_at' => now()->subDays(30 - $i),
                'meta' => $i === 1 ? ['badge' => 'فروش ویژه'] : [],
            ]);

            Course::query()->firstOrCreate(['product_id' => $product->id], [
                'body' => 'متن نمونه برای معرفی دوره.',
                'level' => 'beginner',
                'total_duration_seconds' => 7200,
                'meta' => [],
            ]);
        }
    }
}
