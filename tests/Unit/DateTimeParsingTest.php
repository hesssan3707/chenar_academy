<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DateTimeParsingTest extends TestCase
{
    public function test_it_parses_empty_values_as_null(): void
    {
        $controller = new class extends Controller
        {
            public function parseValue(mixed $value)
            {
                return $this->parseDateTimeOrFail('field', $value);
            }
        };

        $this->assertNull($controller->parseValue(null));
        $this->assertNull($controller->parseValue(''));
        $this->assertNull($controller->parseValue('   '));
    }

    public function test_it_parses_jalali_dates(): void
    {
        $controller = new class extends Controller
        {
            public function parseValue(mixed $value)
            {
                return $this->parseDateTimeOrFail('field', $value);
            }
        };

        $date = $controller->parseValue('1402/01/01');
        $this->assertNotNull($date);
        $this->assertSame('2023-03-21', $date->toDateString());

        $dateTime = $controller->parseValue('1402/01/01 13:45');
        $this->assertNotNull($dateTime);
        $this->assertSame('2023-03-21 13:45:00', $dateTime->format('Y-m-d H:i:s'));

        $dateTimeDash = $controller->parseValue('1402-01-01 13:45');
        $this->assertNotNull($dateTimeDash);
        $this->assertSame('2023-03-21 13:45:00', $dateTimeDash->format('Y-m-d H:i:s'));
    }

    public function test_it_parses_persian_digits(): void
    {
        $controller = new class extends Controller
        {
            public function parseValue(mixed $value)
            {
                return $this->parseDateTimeOrFail('field', $value);
            }
        };

        $dateTime = $controller->parseValue('۱۴۰۲/۰۱/۰۱ ۱۳:۴۵');
        $this->assertNotNull($dateTime);
        $this->assertSame('2023-03-21 13:45:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function test_it_parses_gregorian_dates(): void
    {
        $controller = new class extends Controller
        {
            public function parseValue(mixed $value)
            {
                return $this->parseDateTimeOrFail('field', $value);
            }
        };

        $dateTime = $controller->parseValue('2024-02-01 10:00');
        $this->assertNotNull($dateTime);
        $this->assertSame('2024-02-01 10:00:00', $dateTime->format('Y-m-d H:i:s'));
    }

    public function test_it_throws_validation_exception_on_invalid_dates(): void
    {
        $controller = new class extends Controller
        {
            public function parseValue(mixed $value)
            {
                return $this->parseDateTimeOrFail('field', $value);
            }
        };

        $this->expectException(ValidationException::class);

        $controller->parseValue('not-a-date');
    }
}
