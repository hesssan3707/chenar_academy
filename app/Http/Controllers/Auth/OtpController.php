<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

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
            Log::error('OTP send error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'ok' => false,
                'message' => 'خطای سرور رخ داد. لطفا بعدا دوباره تلاش کنید.',
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

            // Validate SMS credentials exist
            $smsUrl = env("SMS_IR_URL");
            $smsApiKey = env("SMS_IR_API_KEY");
            $smsTemplateId = env("SMS_IR_TEMPLATE_ID");

            if (!$smsUrl || !$smsApiKey || !$smsTemplateId) {
                Log::error('SMS service not configured', [
                    'has_url' => (bool) $smsUrl,
                    'has_api_key' => (bool) $smsApiKey,
                    'has_template_id' => (bool) $smsTemplateId,
                ]);
                throw new \Exception('SMS service configuration error');
            }

            $parameters = [
                [
                    "name" => "CODE",
                    "value" => $code
                ]
            ];

            // Send SMS request
            try {
                $response = Http::timeout(30)->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'text/plain',
                    'x-api-key' => $smsApiKey,
                ])->post($smsUrl, [
                    'Mobile' => $normalizedPhone,
                    'TemplateId' => $smsTemplateId,
                    'parameters' => $parameters,
                ]);

                $result = json_decode($response->getBody()->getContents());

                if (!$result || !isset($result->status)) {
                    Log::error('SMS service returned invalid response', [
                        'phone' => $normalizedPhone,
                        'response' => $response->getBody()->getContents(),
                    ]);
                    throw new \Exception('Invalid SMS response');
                }

                if ($result->status !== 1) {
                    Log::warning('SMS service returned error status', [
                        'phone' => $normalizedPhone,
                        'status' => $result->status ?? null,
                        'message' => $result->message ?? null,
                    ]);
                    throw new \Exception('SMS sending failed');
                }

                // SMS sent successfully, create OTP record
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
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('SMS HTTP request failed', [
                    'phone' => $normalizedPhone,
                    'error' => $e->getMessage(),
                    'status' => $e->response?->status(),
                ]);
                throw new \Exception('Failed to connect to SMS service');
            }
        } catch (\Throwable $e) {
            // Log the error with full details
            Log::error('OTP send internal error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'phone' => $normalizedPhone,
                'purpose' => $purpose,
            ]);
            // Rethrow with generic message
            throw ValidationException::withMessages([
                'otp_code' => 'خطا در ارسال کد. لطفا بعدا دوباره تلاش کنید.',
            ]);
        }
    }
}