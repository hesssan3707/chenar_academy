<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ParseDateTimeOrFailTest extends TestCase
{
    public function test_returns_null_for_empty_value(): void
    {
        config(['app.timezone' => 'UTC']);

        $controller = new class extends \App\Http\Controllers\Controller
        {
            public function parsePublic(string $field, mixed $value): ?Carbon
            {
                return $this->parseDateTimeOrFail($field, $value);
            }
        };

        $this->assertNull($controller->parsePublic('published_at', null));
        $this->assertNull($controller->parsePublic('published_at', ''));
        $this->assertNull($controller->parsePublic('published_at', '   '));
    }

    public function test_parses_jalali_date_without_time(): void
    {
        config(['app.timezone' => 'UTC']);

        $controller = new class extends \App\Http\Controllers\Controller
        {
            public function parsePublic(string $field, mixed $value): ?Carbon
            {
                return $this->parseDateTimeOrFail($field, $value);
            }
        };

        $parsed = $controller->parsePublic('published_at', '1400/01/01');

        $this->assertInstanceOf(Carbon::class, $parsed);
        $this->assertSame('2021-03-21 00:00:00', $parsed->format('Y-m-d H:i:s'));
    }

    public function test_parses_jalali_date_with_time_and_persian_digits(): void
    {
        config(['app.timezone' => 'UTC']);

        $controller = new class extends \App\Http\Controllers\Controller
        {
            public function parsePublic(string $field, mixed $value): ?Carbon
            {
                return $this->parseDateTimeOrFail($field, $value);
            }
        };

        $parsed = $controller->parsePublic('published_at', '۱۴۰۰/۰۱/۰۱ ۱۳:۴۵');

        $this->assertInstanceOf(Carbon::class, $parsed);
        $this->assertSame('2021-03-21 13:45:00', $parsed->format('Y-m-d H:i:s'));
    }

    public function test_rejects_invalid_jalali_date(): void
    {
        config(['app.timezone' => 'UTC']);

        $controller = new class extends \App\Http\Controllers\Controller
        {
            public function parsePublic(string $field, mixed $value): ?Carbon
            {
                return $this->parseDateTimeOrFail($field, $value);
            }
        };

        try {
            $controller->parsePublic('published_at', '1400/13/01');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('published_at', $e->errors());
        }
    }
}
