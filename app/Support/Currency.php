<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;

class Currency
{
    private const DEFAULT = 'IRR';
    private const VALID = ['IRR', 'IRT'];

    private static ?string $currentCurrency = null;

    public static function current(): string
    {
        if (self::$currentCurrency !== null) {
            return self::$currentCurrency;
        }

        $currency = self::DEFAULT;
        if (Schema::hasTable('settings')) {
            $raw = Setting::query()->where('key', 'commerce.currency')->value('value');
            if (is_string($raw)) {
                $raw = strtoupper(trim($raw));
                if (in_array($raw, self::VALID, true)) {
                    $currency = $raw;
                }
            }
        }

        self::$currentCurrency = $currency;

        return $currency;
    }

    public static function label(?string $currency = null): string
    {
        $currency = self::normalize($currency);

        return $currency === 'IRT' ? 'تومان' : 'ریال';
    }

    public static function format(int|string|null $amount, ?string $fromCurrency = null, ?string $toCurrency = null): string
    {
        $value = self::convert((int) ($amount ?? 0), $fromCurrency, $toCurrency);

        return number_format(max(0, $value));
    }

    public static function convert(int $amount, ?string $fromCurrency = null, ?string $toCurrency = null): int
    {
        $from = self::normalize($fromCurrency);
        $to = self::normalize($toCurrency ?? self::current());

        if ($from === $to) {
            return max(0, $amount);
        }

        if ($from === 'IRR' && $to === 'IRT') {
            return (int) floor(max(0, $amount) / 10);
        }

        if ($from === 'IRT' && $to === 'IRR') {
            return max(0, $amount) * 10;
        }

        return max(0, $amount);
    }

    public static function normalize(?string $currency): string
    {
        $currency = strtoupper(trim((string) ($currency ?? '')));

        return in_array($currency, self::VALID, true) ? $currency : self::DEFAULT;
    }
}
