<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use App\Models\PostBlock;
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
        $items = [
            [
                'slug' => 'how-to-plan-final-exam',
                'title' => 'چطور برای امتحان پایان‌ترم برنامه‌ریزی کنیم؟',
                'excerpt' => 'یک برنامه ساده و قابل اجرا برای جمع‌بندی، مرور، و تمرین در هفته‌های آخر.',
                'category' => 'guide',
                'days_ago' => 6,
                'blocks' => [
                    ['type' => 'text', 'text' => "اگر برای امتحان پایان‌ترم دیر شروع کرده‌اید، نگران نباشید.\n\nبا یک برنامه‌ی کوتاه و منظم، می‌توانید در چند روز آخر هم پیشرفت خوبی داشته باشید."],
                    ['type' => 'heading', 'text' => '۱) هدف‌گذاری واقع‌بینانه'],
                    ['type' => 'text', 'text' => "به جای اینکه «همه چیز را کامل» بخوانید، روی فصل‌های پرامتیاز تمرکز کنید.\n\nهر روز یک خروجی مشخص تعریف کنید: حل ۲۰ تست، مرور ۱۵ صفحه، یا نوشتن خلاصه‌ی یک فصل."],
                    ['type' => 'heading', 'text' => '۲) تمرین و مرور را ترکیب کنید'],
                    ['type' => 'text', 'text' => "مطالعه‌ی بدون تمرین، فراموشی را بالا می‌برد.\n\nبعد از هر بخش، چند سوال حل کنید و نکات را به خلاصه‌ی خود اضافه کنید."],
                ],
            ],
            [
                'slug' => 'study-notes-that-work',
                'title' => 'جزوه‌نویسی مؤثر: چطور یادداشت‌های قابل مرور بسازیم؟',
                'excerpt' => 'روش‌هایی برای نوشتن جزوه‌ای که شب امتحان واقعاً به کارتان بیاید.',
                'category' => 'learning',
                'days_ago' => 4,
                'blocks' => [
                    ['type' => 'text', 'text' => "جزوه‌ی خوب یعنی «قابل مرور بودن».\n\nاگر یادداشت‌های شما پر از جمله‌های طولانی و بدون ساختار است، مرور آن سخت می‌شود."],
                    ['type' => 'heading', 'text' => 'قانون ۳ رنگ'],
                    ['type' => 'text', 'text' => "یک رنگ برای تیتر، یک رنگ برای فرمول‌ها و نکات کلیدی، و یک رنگ برای خطاهای رایج.\n\nاین کار سرعت مرور را زیاد می‌کند."],
                ],
            ],
            [
                'slug' => 'how-to-learn-from-videos',
                'title' => 'چطور از ویدیوهای آموزشی بیشترین نتیجه را بگیریم؟',
                'excerpt' => 'تماشا کردن کافی نیست؛ نکات مهم برای تمرین، توقف، و یادگیری فعال.',
                'category' => 'learning',
                'days_ago' => 2,
                'blocks' => [
                    ['type' => 'text', 'text' => "ویدیو را مثل کلاس حضوری ببینید: توقف کنید، تمرین کنید، و دوباره ادامه دهید.\n\nیادگیری فعال یعنی شما همزمان درگیر حل مسئله باشید."],
                    ['type' => 'heading', 'text' => 'توقف‌های برنامه‌ریزی‌شده'],
                    ['type' => 'text', 'text' => "هر ۷ تا ۱۰ دقیقه یک توقف کوتاه داشته باشید و نکات را یادداشت کنید.\n\nاگر موضوع محاسباتی است، همان لحظه یک مثال حل کنید."],
                ],
            ],
            [
                'slug' => 'common-mistakes-in-math',
                'title' => 'اشتباهات رایج در ریاضی (و چطور از آن‌ها جلوگیری کنیم)',
                'excerpt' => 'چند خطای پرتکرار که می‌تواند نمره را کم کند و راهکارهای ساده برای پیشگیری.',
                'category' => 'guide',
                'days_ago' => 1,
                'blocks' => [
                    ['type' => 'text', 'text' => "بیشتر اشتباهات ریاضی از بی‌دقتی است، نه از ندانستن.\n\nبا چند عادت ساده می‌توانید درصد خطا را کم کنید."],
                    ['type' => 'heading', 'text' => 'بازبینی واحدها و علامت‌ها'],
                    ['type' => 'text', 'text' => "قبل از نهایی کردن جواب، واحدها و علامت‌های منفی/مثبت را سریع چک کنید.\n\nدر فیزیک و محاسبات، این مورد بسیار پرتکرار است."],
                ],
            ],
            [
                'slug' => 'exam-night-checklist',
                'title' => 'چک‌لیست شب امتحان: چه کار کنیم و چه کار نکنیم؟',
                'excerpt' => 'چند توصیه عملی برای شب امتحان تا تمرکز و انرژی را حفظ کنید.',
                'category' => 'news',
                'days_ago' => 0,
                'blocks' => [
                    ['type' => 'text', 'text' => "شب امتحان زمان یادگیری عمیق نیست؛ زمان مرور و جمع‌بندی است.\n\nروی خلاصه‌ها، نکات کلیدی، و نمونه سوال‌ها تمرکز کنید."],
                    ['type' => 'heading', 'text' => 'خواب را قربانی نکنید'],
                    ['type' => 'text', 'text' => "کم‌خوابی باعث کاهش دقت و افت عملکرد می‌شود.\n\nیک خواب کوتاه و باکیفیت ارزشمندتر از چند ساعت مطالعه‌ی بی‌تمرکز است."],
                ],
            ],
        ];

        foreach ($items as $item) {
            $post = Post::query()->updateOrCreate(['slug' => $item['slug']], [
                'author_user_id' => null,
                'title' => $item['title'],
                'excerpt' => $item['excerpt'],
                'status' => 'published',
                'published_at' => now()->subDays((int) $item['days_ago']),
                'cover_media_id' => null,
                'meta' => [],
            ]);

            $categorySlug = (string) ($item['category'] ?? '');
            if ($categorySlug !== '') {
                $category = Category::query()->where('type', 'post')->where('slug', $categorySlug)->first();
                if ($category) {
                    $post->categories()->syncWithoutDetaching([$category->id]);
                }
            }

            foreach (($item['blocks'] ?? []) as $index => $block) {
                $blockType = (string) ($block['type'] ?? 'text');
                $text = (string) ($block['text'] ?? '');

                PostBlock::query()->updateOrCreate([
                    'post_id' => $post->id,
                    'sort_order' => $index,
                ], [
                    'block_type' => $blockType,
                    'text' => $text,
                    'media_id' => null,
                    'meta' => [],
                ]);
            }
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
