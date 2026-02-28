<?php

namespace Database\Seeders;

use App\Models\CategoryType;
use Illuminate\Database\Seeder;

class CategoryTypeSeeder extends Seeder
{
    public function run(): void
    {
        CategoryType::clearTypeCache();

        $items = [
            ['key' => 'institution', 'title' => 'دانشگاه'],
            ['key' => 'video', 'title' => 'ویدیو و دوره'],
            ['key' => 'note', 'title' => 'جزوه'],
            ['key' => 'ticket', 'title' => 'تیکت'],
            ['key' => 'post', 'title' => 'مقاله'],
        ];

        foreach ($items as $item) {
            CategoryType::query()->updateOrCreate(
                ['key' => $item['key']],
                ['title' => $item['title']],
            );
        }

        CategoryType::clearTypeCache();
    }
}
