<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $institutionIds = $this->ensureInstitutionCategories($now);
        $subjectsByInstitution = $this->subjectsByInstitutionFromProductsCsv();

        foreach ($subjectsByInstitution as $institutionSlug => $subjects) {
            $institutionId = (int) ($institutionIds[$institutionSlug] ?? 0);
            if ($institutionId <= 0) {
                continue;
            }

            foreach ($subjects as $subjectTitle => $subjectTypes) {
                if (isset($subjectTypes['note'])) {
                    $this->ensureCategory([
                        'type' => 'note',
                        'parent_id' => $institutionId,
                        'title' => $subjectTitle,
                        'slug' => $this->buildCategorySlug($institutionSlug, $subjectTitle),
                        'icon_key' => $this->detectCategoryIconKey($subjectTitle, 'note'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if (isset($subjectTypes['video']) || isset($subjectTypes['course'])) {
                    $slug = $this->buildCategorySlug($institutionSlug, $subjectTitle);
                    $this->ensureCategory([
                        'type' => 'video',
                        'parent_id' => $institutionId,
                        'title' => $subjectTitle,
                        'slug' => $slug,
                        'icon_key' => $this->detectCategoryIconKey($subjectTitle, 'video'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $this->ensureCategory([
                        'type' => 'course',
                        'parent_id' => $institutionId,
                        'title' => $subjectTitle,
                        'slug' => $slug,
                        'icon_key' => $this->detectCategoryIconKey($subjectTitle, 'course'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function ensureInstitutionCategories(\Illuminate\Support\Carbon $now): array
    {
        $map = [
            'pnu' => [
                'title' => 'دانشگاه پیام نور',
                'slug' => 'pnu',
            ],
            'uni' => [
                'title' => 'دانشگاه آزاد و دولتی',
                'slug' => 'uni',
            ],
        ];

        $ids = [];
        foreach ($map as $key => $data) {
            $id = DB::table('categories')
                ->where('type', 'institution')
                ->where('slug', $data['slug'])
                ->value('id');

            if (! $id) {
                $id = DB::table('categories')->insertGetId([
                    'type' => 'institution',
                    'parent_id' => null,
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'icon_key' => 'university',
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $ids[$key] = (int) $id;
        }

        return $ids;
    }

    private function subjectsByInstitutionFromProductsCsv(): array
    {
        $csv = ProductionSeedData::productsCsv();
        $rows = preg_split("/\r\n|\n|\r/", $csv);
        if (! is_array($rows) || count($rows) < 2) {
            return [];
        }

        $result = [];
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
            $categoryNames = trim((string) ($row[11] ?? ''));

            $institutionSlug = $this->detectInstitutionSlug($title, $categoryNames);
            $type = $this->detectProductType($title, $description);
            $subjectTitle = $this->detectSubjectTitle($title, $categoryNames);

            if (! isset($result[$institutionSlug])) {
                $result[$institutionSlug] = [];
            }

            if (! isset($result[$institutionSlug][$subjectTitle])) {
                $result[$institutionSlug][$subjectTitle] = [];
            }

            $result[$institutionSlug][$subjectTitle][$type] = true;
        }

        return $result;
    }

    private function ensureCategory(array $data): int
    {
        $existingId = DB::table('categories')
            ->where('type', $data['type'])
            ->where('slug', $data['slug'])
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) DB::table('categories')->insertGetId([
            'type' => $data['type'],
            'parent_id' => $data['parent_id'],
            'title' => $data['title'],
            'slug' => $data['slug'],
            'icon_key' => $data['icon_key'],
            'description' => null,
            'is_active' => true,
            'sort_order' => 0,
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ]);
    }

    private function detectInstitutionSlug(string $title, string $categoryNames): string
    {
        $text = $categoryNames !== '' ? $categoryNames : $title;

        if (Str::contains($text, 'پیام')) {
            return 'pnu';
        }

        return 'uni';
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

    private function detectCategoryIconKey(string $subjectTitle, string $productType): ?string
    {
        if ($productType === 'video' || $productType === 'course') {
            if (Str::contains($subjectTitle, 'ریاضی')) {
                return 'math';
            }
            if (Str::contains($subjectTitle, 'فیزیک')) {
                return 'physics';
            }

            return 'video';
        }

        if (Str::contains($subjectTitle, 'ریاضی')) {
            return 'math';
        }
        if (Str::contains($subjectTitle, 'فیزیک')) {
            return 'physics';
        }

        return null;
    }
}
