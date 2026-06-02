<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'currency',
        'subtotal_amount',
        'discount_amount',
        'total_amount',
        'payable_amount',
        'placed_at',
        'paid_at',
        'cancelled_at',
        'meta',
    ];

    protected $casts = [
        'placed_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id')->orderBy('id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id')->orderByDesc('id');
    }

    public function currencyCode(): string
    {
        $currency = strtoupper((string) ($this->currency ?? 'IRR'));

        return in_array($currency, ['IRR', 'IRT'], true) ? $currency : 'IRR';
    }

    public function displaySubtotalAmount(?string $currency = null): int
    {
        return Currency::convert((int) ($this->subtotal_amount ?? 0), $this->currencyCode(), $currency);
    }

    public function displayDiscountAmount(?string $currency = null): int
    {
        return Currency::convert((int) ($this->discount_amount ?? 0), $this->currencyCode(), $currency);
    }

    public function displayTotalAmount(?string $currency = null): int
    {
        return Currency::convert((int) ($this->total_amount ?? 0), $this->currencyCode(), $currency);
    }

    public function displayPayableAmount(?string $currency = null): int
    {
        return Currency::convert((int) ($this->payable_amount ?? 0), $this->currencyCode(), $currency);
    }
}
