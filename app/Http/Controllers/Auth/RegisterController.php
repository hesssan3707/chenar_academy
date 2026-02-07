<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:160'],
            'password' => ['required', 'string', 'min:6', 'max:120', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'max:120'],
            'otp_code' => ['required', 'string', 'max:10'],
        ]);

        if (User::query()->where('phone', $validated['phone'])->exists()) {
            throw ValidationException::withMessages([
                'phone' => 'این شماره موبایل قبلا ثبت نام شده است.',
            ]);
        }

        $this->consumeOtpOrFail($validated['phone'], 'register', (string) $validated['otp_code']);

        $fullName = trim((string) ($validated['name'] ?? '')) ?: $validated['phone'];

        $user = User::query()->create([
            'name' => $fullName,
            'email' => null,
            'password' => $validated['password'],
            'phone' => $validated['phone'],
            'phone_verified_at' => now(),
            'is_active' => true,
        ]);

        if (! $user->phone_verified_at) {
            $user->forceFill(['phone_verified_at' => now()])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuth($user);
    }

    private function consumeOtpOrFail(string $phone, string $purpose, string $code): void
    {
        $otp = OtpCode::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'otp_code' => 'کد نامعتبر است یا منقضی شده است.',
            ]);
        }

        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) {
            throw ValidationException::withMessages([
                'otp_code' => 'کد نامعتبر است.',
            ]);
        }

        $otp->forceFill(['consumed_at' => now()])->save();
    }

    private function redirectAfterAuth(User $user): RedirectResponse
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('panel.dashboard');
    }
}
