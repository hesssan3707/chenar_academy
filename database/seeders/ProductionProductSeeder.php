<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $institutionIds = $this->institutionIds();

        $csv = ProductionSeedData::productsCsv();
        $rows = preg_split("/\r\n|\n|\r/", $csv);
        if (! is_array($rows) || count($rows) < 2) {
            return;
        }

        $headerSkipped = false;

        foreach ($rows as $line) {
            if (! is_string($line) || trim($line) === '') {
                continue;
            }

            if (! $headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $row = str_getcsv($line);
            $id = (int) ($row[0] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $title = trim((string) ($row[3] ?? ''));
            $description = (string) ($row[4] ?? '');
            $slugFromCsv = trim((string) ($row[5] ?? ''));
            $metaDescription = trim((string) ($row[7] ?? ''));
            $imagesUrl = trim((string) ($row[9] ?? ''));
            $categoryNames = trim((string) ($row[11] ?? ''));
            $visibleRaw = trim((string) ($row[16] ?? ''));
            $price = (int) ($row[17] ?? 0);

            if ($title === '') {
                $title = 'محصول '.$id;
            }

            $type = $this->detectProductType($title, $description);
            $institutionSlug = $this->detectInstitutionSlug($title, $categoryNames);
            $institutionCategoryId = (int) ($institutionIds[$institutionSlug] ?? 0);

            $subjectTitle = $this->detectSubjectTitle($title, $categoryNames);
            $categorySlug = $this->buildCategorySlug($institutionSlug, $subjectTitle);

            $categoryType = $type === 'note' ? 'note' : ($type === 'course' ? 'course' : 'video');
            $categoryId = (int) DB::table('categories')
                ->where('type', $categoryType)
                ->where('slug', $categorySlug)
                ->where('parent_id', $institutionCategoryId)
                ->value('id');

            $slug = $slugFromCsv !== '' ? $slugFromCsv : 'p-'.$id;
            $slug = $this->ensureUniqueProductSlug($slug, $id);

            $status = $this->truthy($visibleRaw) ? 'published' : 'draft';

            DB::table('products')->insert([
                'id' => $id,
                'type' => $type,
                'title' => $title,
                'slug' => $slug,
                'excerpt' => $metaDescription !== '' ? $metaDescription : null,
                'description' => $description !== '' ? $description : null,
                'thumbnail_media_id' => null,
                'institution_category_id' => $institutionCategoryId,
                'status' => $status,
                'base_price' => $price,
                'sale_price' => null,
                'discount_type' => null,
                'discount_value' => null,
                'currency' => 'IRR',
                'published_at' => null,
                'meta' => json_encode([
                    'images_url' => $imagesUrl !== '' ? $imagesUrl : null,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($categoryId > 0) {
                DB::table('product_categories')->insert([
                    'product_id' => $id,
                    'category_id' => $categoryId,
                ]);
            }

            if ($type === 'course') {
                DB::table('courses')->insert([
                    'product_id' => $id,
                    'body' => null,
                    'level' => null,
                    'total_duration_seconds' => null,
                    'meta' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function institutionIds(): array
    {
        $bySlug = DB::table('categories')
            ->where('type', 'institution')
            ->whereIn('slug', ['pnu', 'uni'])
            ->pluck('id', 'slug')
            ->all();

        return [
            'pnu' => (int) ($bySlug['pnu'] ?? 0),
            'uni' => (int) ($bySlug['uni'] ?? 0),
        ];
    }

    private function detectProductType(string $title, string $description): string
    {
        if (Str::contains($title, 'جزوه')) {
            return 'note';
        }

        if (Str::contains($title, 'دوره')) {
            return 'course';
        }

        if (Str::contains($description, 'player.arvancloud.ir')) {
            return 'video';
        }

        return 'video';
    }

    private function detectInstitutionSlug(string $title, string $categoryNames): string
    {
        $text = $categoryNames !== '' ? $categoryNames : $title;

        if (Str::contains($text, 'پیام')) {
            return 'pnu';
        }

        return 'uni';
    }

    private function detectSubjectTitle(string $title, string $categoryNames): string
    {
        $parts = array_values(array_filter(array_map('trim', explode(',', $categoryNames)), fn ($v) => $v !== ''));
        if (count($parts) >= 2) {
            return $parts[1];
        }

        $clean = trim($title);
        $clean = str_replace(['پیام نور', 'دانشگاه آزاد', 'آزاد', 'دولتی', 'سراسری'], '', $clean);
        $clean = preg_replace('/\s+/u', ' ', (string) $clean);
        $clean = trim((string) $clean);

        return $clean !== '' ? $clean : 'عمومی';
    }

    private function buildCategorySlug(string $institutionSlug, string $subjectTitle): string
    {
        $subjectSlug = $this->persianSlug($subjectTitle);
        $base = $institutionSlug.'-'.$subjectSlug;

        return mb_substr($base, 0, 180);
    }

    private function persianSlug(string $value): string
    {
        $value = trim($value);
        $value = str_replace(['‌', 'ـ'], ' ', $value);
        $value = preg_replace('/[^\p{L}\p{N}\s\-]+/u', '', $value);
        $value = preg_replace('/\s+/u', '-', (string) $value);
        $value = trim((string) $value, '-');

        return $value !== '' ? $value : 'general';
    }

    private function ensureUniqueProductSlug(string $slug, int $productId): string
    {
        $candidate = $slug;
        if (! DB::table('products')->where('slug', $candidate)->exists()) {
            return $candidate;
        }

        $candidate = $slug.'-'.$productId;
        if (! DB::table('products')->where('slug', $candidate)->exists()) {
            return $candidate;
        }

        return 'p-'.$productId;
    }

    private function truthy(string $value): bool
    {
        $v = mb_strtolower(trim($value));
        return in_array($v, ['1', 'true', 'yes', 'y', 'on', 't'], true);
    }
}

