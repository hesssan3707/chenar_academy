<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'description',
        'thumbnail_media_id',
        'status',
        'base_price',
        'sale_price',
        'currency',
        'published_at',
        'meta',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    public function accesses(): HasMany
    {
        return $this->hasMany(ProductAccess::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(ProductPart::class, 'product_id')->orderBy('sort_order')->orderBy('id');
    }

    public function course(): HasOne
    {
        return $this->hasOne(Course::class, 'product_id');
    }

    public function video(): HasOne
    {
        return $this->hasOne(Video::class, 'product_id');
    }

    public function userHasAccess(User $user): bool
    {
        return ProductAccess::query()
            ->where('user_id', $user->id)
            ->where('product_id', $this->id)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
