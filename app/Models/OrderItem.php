<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_type',
        'product_title',
        'quantity',
        'unit_price',
        'total_price',
        'currency',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function currencyCode(): string
    {
        $currency = strtoupper((string) ($this->currency ?? ($this->order?->currency ?? 'IRR')));

        return in_array($currency, ['IRR', 'IRT'], true) ? $currency : 'IRR';
    }

    public function displayTotalPrice(?string $currency = null): int
    {
        return Currency::convert((int) ($this->total_price ?? 0), $this->currencyCode(), $currency);
    }
}
