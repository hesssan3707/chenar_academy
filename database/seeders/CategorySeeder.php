<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $ticketTypeId = Category::typeId('ticket');
        $postTypeId = Category::typeId('post');
        $institutionTypeId = Category::typeId('institution');

        $ticketCategories = [
            ['title' => 'Technical Support', 'slug' => 'technical-support'],
            ['title' => 'Billing & Orders', 'slug' => 'billing-orders'],
            ['title' => 'General Questions', 'slug' => 'general-questions'],
            ['title' => 'Other', 'slug' => 'other'],
        ];

        foreach ($ticketCategories as $item) {
            Category::query()->updateOrCreate(
                ['category_type_id' => $ticketTypeId, 'slug' => $item['slug']],
                [
                    'parent_id' => null,
                    'title' => $item['title'],
                    'icon_key' => null,
                    'description' => null,
                    'cover_media_id' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }

        $articleCategories = [
            ['title' => 'News', 'slug' => 'news'],
            ['title' => 'Guides', 'slug' => 'guides'],
            ['title' => 'Learning', 'slug' => 'learning'],
            ['title' => 'Announcements', 'slug' => 'announcements'],
        ];

        foreach ($articleCategories as $item) {
            Category::query()->updateOrCreate(
                ['category_type_id' => $postTypeId, 'slug' => $item['slug']],
                [
                    'parent_id' => null,
                    'title' => $item['title'],
                    'icon_key' => null,
                    'description' => null,
                    'cover_media_id' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }

        $universities = [
            ['title' => 'Payame Noor University', 'slug' => 'pnu'],
            ['title' => 'State University', 'slug' => 'state'],
            ['title' => 'Islamic Azad University', 'slug' => 'iau'],
        ];

        foreach ($universities as $item) {
            Category::query()->updateOrCreate(
                ['category_type_id' => $institutionTypeId, 'slug' => $item['slug']],
                [
                    'parent_id' => null,
                    'title' => $item['title'],
                    'icon_key' => 'university',
                    'description' => null,
                    'cover_media_id' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ],
            );
        }
    }
}
