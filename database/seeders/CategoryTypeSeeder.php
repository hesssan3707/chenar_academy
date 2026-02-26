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
            ['key' => 'institution', 'title' => 'University'],
            ['key' => 'video', 'title' => 'Course & Video'],
            ['key' => 'note', 'title' => 'Booklet'],
            ['key' => 'ticket', 'title' => 'Ticket'],
            ['key' => 'post', 'title' => 'Article'],
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
