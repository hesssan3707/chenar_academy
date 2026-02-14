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

    public function currencyCode(): string
    {
        $currency = strtoupper((string) ($this->currency ?? 'IRR'));

        return in_array($currency, ['IRR', 'IRT'], true) ? $currency : 'IRR';
    }

    private function currencyLabelFor(string $currency): string
    {
        $currency = strtoupper($currency);

        return $currency === 'IRT' ? 'تومان' : 'ریال';
    }

    private function convertCurrencyAmount(int $amount, string $from, string $to): int
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return $amount;
        }

        if ($from === 'IRR' && $to === 'IRT') {
            return (int) floor(max(0, $amount) / 10);
        }

        if ($from === 'IRT' && $to === 'IRR') {
            return max(0, $amount) * 10;
        }

        return $amount;
    }

    public function displayOriginalPrice(string $currency): int
    {
        return $this->convertCurrencyAmount($this->originalPrice(), $this->currencyCode(), $currency);
    }

    public function displayFinalPrice(string $currency): int
    {
        return $this->convertCurrencyAmount($this->finalPrice(), $this->currencyCode(), $currency);
    }

    private function formatPersianNumber(int $value): string
    {
        $formatted = number_format(max(0, $value));
        $formatted = str_replace(',', '٬', $formatted);

        return strtr($formatted, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
        ]);
    }

    public function discountLabel(): ?string
    {
        return $this->discountLabelFor($this->currencyCode());
    }

    public function discountLabelFor(string $currency): ?string
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        $currency = strtoupper($currency);
        if (! in_array($currency, ['IRR', 'IRT'], true)) {
            $currency = 'IRR';
        }

        $discountType = (string) ($this->discount_type ?? '');
        $discountValue = (int) ($this->discount_value ?? 0);

        if ($discountType === 'percent' && $discountValue > 0) {
            $percent = max(0, min(100, $discountValue));

            return $this->formatPersianNumber($percent).'٪ تخفیف';
        }

        if ($discountType === 'amount' && $discountValue > 0) {
            $displayAmount = $this->convertCurrencyAmount($discountValue, $this->currencyCode(), $currency);

            return $this->formatPersianNumber($displayAmount).' '.$this->currencyLabelFor($currency).' تخفیف';
        }

        $base = $this->originalPrice();
        $final = $this->finalPrice();
        if ($base <= 0 || $final >= $base) {
            return null;
        }

        $percent = (int) round((($base - $final) / $base) * 100);
        $percent = max(1, min(99, $percent));

        return $this->formatPersianNumber($percent).'٪ تخفیف';
    }
}
