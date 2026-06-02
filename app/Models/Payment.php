<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',
        'status',
        'amount',
        'currency',
        'authority',
        'reference_id',
        'paid_at',
        'meta',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'meta' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function currencyCode(): string
    {
        $currency = strtoupper((string) ($this->currency ?? 'IRR'));

        return in_array($currency, ['IRR', 'IRT'], true) ? $currency : 'IRR';
    }

    public function displayAmount(?string $currency = null): int
    {
        return Currency::convert((int) ($this->amount ?? 0), $this->currencyCode(), $currency);
    }
}
