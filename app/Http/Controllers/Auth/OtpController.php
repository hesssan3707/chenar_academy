<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'purpose' => ['required', 'string', Rule::in(['login', 'register', 'password_reset'])],
        ]);

        if ($validated['purpose'] === 'password_reset') {
            if (! User::query()->where('phone', $validated['phone'])->exists()) {
                throw ValidationException::withMessages([
                    'phone' => 'کاربری با این شماره تلفن پیدا نشد.',
                ]);
            }
        }

        $this->sendOtp($request, $validated['phone'], $validated['purpose']);

        return response()->json([
            'ok' => true,
            'purpose' => $validated['purpose'],
            'phone' => $validated['phone'],
            'cooldown_seconds' => 120,
            'toast' => [
                'type' => 'success',
                'title' => 'کد ارسال شد',
                'message' => 'کد یکبار مصرف ارسال شد. لطفا پیامک را بررسی کنید.',
            ],
        ]);
    }

    private function sendOtp(Request $request, string $phone, string $purpose): void
    {
        $cooldownKey = sprintf('otp.cooldown:%s:%s', $purpose, preg_replace('/\D+/', '', $phone) ?: $phone);

        if (! Cache::add($cooldownKey, true, now()->addSeconds(120))) {
            throw ValidationException::withMessages([
                'otp_code' => 'برای ارسال مجدد کد، لطفا ۲ دقیقه صبر کنید.',
            ]);
        }

        $code = app()->isProduction()
            ? (string) random_int(10000, 99999)
            : '11111';

        OtpCode::query()->create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'consumed_at' => null,
            'attempts' => 0,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);
    }
}
