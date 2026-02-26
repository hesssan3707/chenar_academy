<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class CategoryType extends Model
{
    protected $fillable = [
        'key',
        'title',
    ];

    private static ?array $idByKeyCache = null;

    private static ?array $keyByIdCache = null;

    public static function idForKey(string $key): int
    {
        $key = trim($key);
        if ($key === '') {
            return 0;
        }

        self::primeCache();

        $id = (int) (self::$idByKeyCache[$key] ?? 0);
        if ($id > 0) {
            if (self::cacheMappingIsValid($id, $key)) {
                return $id;
            }

            self::clearTypeCache();
            self::primeCache();
            $id = (int) (self::$idByKeyCache[$key] ?? 0);
            if ($id > 0 && self::cacheMappingIsValid($id, $key)) {
                return $id;
            }
        }

        if (! Schema::hasTable('category_types')) {
            return 0;
        }

        $id = (int) (self::query()->where('key', $key)->value('id') ?? 0);
        if ($id <= 0) {
            $row = self::query()->firstOrCreate(
                ['key' => $key],
                ['title' => $key],
            );
            $id = (int) ($row->id ?? 0);
        }

        if ($id > 0) {
            self::$idByKeyCache[$key] = $id;
            self::$keyByIdCache[$id] = $key;
        }

        return $id;
    }

    public static function keyForId(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        self::primeCache();

        $key = self::$keyByIdCache[$id] ?? null;
        if (is_string($key) && $key !== '') {
            if (self::cacheMappingIsValid($id, $key)) {
                return $key;
            }

            self::clearTypeCache();
            self::primeCache();
            $key = self::$keyByIdCache[$id] ?? null;
            if (is_string($key) && $key !== '' && self::cacheMappingIsValid($id, $key)) {
                return $key;
            }
        }

        if (! Schema::hasTable('category_types')) {
            return null;
        }

        $key = self::query()->whereKey($id)->value('key');
        $key = is_string($key) ? trim($key) : '';
        if ($key === '') {
            return null;
        }

        self::$keyByIdCache[$id] = $key;
        self::$idByKeyCache[$key] = $id;

        return $key;
    }

    public static function clearTypeCache(): void
    {
        self::$idByKeyCache = null;
        self::$keyByIdCache = null;
    }

    private static function cacheMappingIsValid(int $id, string $key): bool
    {
        if ($id <= 0 || $key === '' || ! Schema::hasTable('category_types')) {
            return false;
        }

        $actualKey = self::query()->whereKey($id)->value('key');

        return is_string($actualKey) && trim($actualKey) === $key;
    }

    private static function primeCache(): void
    {
        if (is_array(self::$idByKeyCache) && is_array(self::$keyByIdCache)) {
            return;
        }

        self::$idByKeyCache = [];
        self::$keyByIdCache = [];

        if (! Schema::hasTable('category_types')) {
            return;
        }

        $rows = self::query()->get(['id', 'key']);
        foreach ($rows as $row) {
            $id = (int) ($row->id ?? 0);
            $key = trim((string) ($row->key ?? ''));
            if ($id <= 0 || $key === '') {
                continue;
            }

            self::$idByKeyCache[$key] = $id;
            self::$keyByIdCache[$id] = $key;
        }
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'category_type_id');
    }
}
