<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\CalendarUtils;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function parseDateTimeOrFail(string $field, mixed $value): ?Carbon
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '') {
            return null;
        }

        $raw = strtr($raw, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);

        try {
            if (preg_match(
                '/^(?<y>\\d{4})[\\/\\-](?<m>\\d{1,2})[\\/\\-](?<d>\\d{1,2})(?:\\s+(?<hh>\\d{1,2}):(?<ii>\\d{1,2})(?::(?<ss>\\d{1,2}))?)?$/',
                $raw,
                $matches
            ) === 1) {
                $year = (int) $matches['y'];
                if ($year >= 1300 && $year <= 1500) {
                    $month = (int) $matches['m'];
                    $day = (int) $matches['d'];

                    $hour = isset($matches['hh']) && $matches['hh'] !== '' ? (int) $matches['hh'] : 0;
                    $minute = isset($matches['ii']) && $matches['ii'] !== '' ? (int) $matches['ii'] : 0;
                    $second = isset($matches['ss']) && $matches['ss'] !== '' ? (int) $matches['ss'] : 0;

                    if (! CalendarUtils::isValidateJalaliDate($year, $month, $day)) {
                        throw ValidationException::withMessages([
                            $field => ['فرمت تاریخ نامعتبر است.'],
                        ]);
                    }

                    $timestamp = sprintf('%04d/%02d/%02d', $year, $month, $day);
                    $format = 'Y/m/d';

                    if (isset($matches['hh']) && $matches['hh'] !== '') {
                        $timestamp .= sprintf(' %02d:%02d', $hour, $minute);
                        $format .= ' H:i';

                        if (isset($matches['ss']) && $matches['ss'] !== '') {
                            $timestamp .= sprintf(':%02d', $second);
                            $format .= ':s';
                        }
                    }

                    $carbon = CalendarUtils::createCarbonFromFormat($format, $timestamp, config('app.timezone'));

                    return Carbon::instance($carbon);
                }
            }

            return Carbon::parse($raw, config('app.timezone'));
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => ['فرمت تاریخ نامعتبر است.'],
            ]);
        }
    }
}
