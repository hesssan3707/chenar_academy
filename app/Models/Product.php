<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'institution_category_id',
        'status',
        'base_price',
        'sale_price',
        'discount_type',
        'discount_value',
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

    public function institutionCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'institution_category_id');
    }

    public function thumbnailMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'thumbnail_media_id');
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

    public function originalPrice(): int
    {
        return max(0, (int) ($this->base_price ?? 0));
    }

    public function finalPrice(): int
    {
        $basePrice = $this->originalPrice();

        $discountType = (string) ($this->discount_type ?? '');
        $discountValue = (int) ($this->discount_value ?? 0);

        if ($discountType !== '' && $discountValue > 0) {
            if ($discountType === 'percent') {
                $percent = max(0, min(100, $discountValue));

                return (int) floor($basePrice * (100 - $percent) / 100);
            }

            if ($discountType === 'amount') {
                return max(0, $basePrice - $discountValue);
            }
        }

        $salePrice = $this->sale_price;
        if ($salePrice !== null && (string) $salePrice !== '') {
            $sale = max(0, (int) $salePrice);

            if ($basePrice <= 0) {
                return $sale;
            }

            return min($basePrice, $sale);
        }

        return $basePrice;
    }

    public function discountAmount(): int
    {
        return max(0, $this->originalPrice() - $this->finalPrice());
    }

    public function hasDiscount(): bool
    {
        return $this->discountAmount() > 0;
    }

    public function discountLabel(): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        $discountType = (string) ($this->discount_type ?? '');
        $discountValue = (int) ($this->discount_value ?? 0);

        if ($discountType === 'percent' && $discountValue > 0) {
            $percent = max(0, min(100, $discountValue));

            return $percent.'% OFF';
        }

        if ($discountType === 'amount' && $discountValue > 0) {
            return number_format($discountValue).' OFF';
        }

        $base = $this->originalPrice();
        $final = $this->finalPrice();
        if ($base <= 0 || $final >= $base) {
            return null;
        }

        $percent = (int) round((($base - $final) / $base) * 100);
        $percent = max(1, min(99, $percent));

        return $percent.'% OFF';
    }
}
