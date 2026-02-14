<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'starts_at',
        'ends_at',
        'usage_limit',
        'per_user_limit',
        'used_count',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class, 'coupon_id')->orderByDesc('redeemed_at')->orderByDesc('id');
    }

    public function productIds(): array
    {
        $raw = ($this->meta ?? [])['product_ids'] ?? null;
        if (! is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function appliesToAllProducts(): bool
    {
        return count($this->productIds()) === 0;
    }
}
