<?php

namespace App\Models;

use App\Models\Builders\CategoryBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category_type_id',
        'parent_id',
        'title',
        'slug',
        'icon_key',
        'description',
        'cover_media_id',
        'is_active',
        'sort_order',
    ];

    public function newEloquentBuilder($query): Builder
    {
        return new CategoryBuilder($query);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function setTypeAttribute(?string $value): void
    {
        $key = self::normalizeTypeKey((string) $value);
        $id = CategoryType::idForKey($key);
        if ($id > 0) {
            $this->attributes['category_type_id'] = $id;
        }
    }

    public function getTypeAttribute(): string
    {
        $key = (string) ($this->categoryType?->key ?? '');
        if ($key !== '') {
            return $key;
        }

        $key = (string) (CategoryType::keyForId((int) ($this->category_type_id ?? 0)) ?? '');

        return $key;
    }

    public function scopeOfType(Builder $query, string $key): Builder
    {
        $id = self::typeId($key);
        if ($id <= 0) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('category_type_id', $id);
    }

    public function scopeOfTypes(Builder $query, array $keys): Builder
    {
        $ids = self::typeIds($keys);
        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('category_type_id', $ids);
    }

    public static function typeId(string $key): int
    {
        return CategoryType::idForKey(self::normalizeTypeKey($key));
    }

    public static function typeIds(array $keys): array
    {
        $ids = [];
        foreach ($keys as $key) {
            $id = self::typeId((string) $key);
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    public static function normalizeTypeKey(string $key): string
    {
        $normalized = trim($key);

        return $normalized === 'course' ? 'video' : $normalized;
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories', 'category_id', 'product_id');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }
}
