<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => ['required', 'string', 'max:20'],
                'purpose' => ['required', 'string', Rule::in(['login', 'admin_login', 'register', 'password_reset'])],
            ]);

            if (in_array($validated['purpose'], ['login', 'admin_login', 'password_reset'], true)) {
                $user = User::query()->where('phone', $validated['phone'])->first();

                if (! $user) {
                    throw ValidationException::withMessages([
                        'phone' => 'کاربری با این شماره تلفن پیدا نشد.',
                    ]);
                }

                if ($validated['purpose'] === 'admin_login' && ! $user->hasRole('admin')) {
                    throw ValidationException::withMessages([
                        'phone' => 'دسترسی ورود مدیر ندارید.',
                    ]);
                }
            }

            $this->sendOtp($request, $validated['phone'], $validated['purpose']);

            return response()->json([
                'ok' => true,
                'purpose' => $validated['purpose'],
                'phone' => $validated['phone'],
                'cooldown_seconds' => 60,
                'toast' => [
                    'type' => 'success',
                    'title' => 'کد ارسال شد',
                    'message' => 'کد یکبار مصرف ارسال شد. لطفا پیامک را بررسی کنید.',
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'خطای سرور: '.$e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }

    private function sendOtp(Request $request, string $phone, string $purpose): void
    {
        $normalizedPhone = preg_replace('/\D+/', '', $phone) ?: $phone;
        $throttleKey = sprintf('otp.send:%s:%s', $purpose, $normalizedPhone);

        if (RateLimiter::tooManyAttempts($throttleKey, 2)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'otp_code' => sprintf('تعداد درخواست زیاد است. لطفا %d ثانیه دیگر تلاش کنید.', max(1, (int) $seconds)),
            ]);
        }

        try {
            RateLimiter::hit($throttleKey, 60);

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
        } catch (\Throwable $e) {
            // Log the error or rethrow it to be caught by the calling method
            throw $e;
        }
    }
}
