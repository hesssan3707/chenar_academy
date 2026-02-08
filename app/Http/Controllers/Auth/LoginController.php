<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function showAdmin(): View
    {
        return view('admin.auth.login');
    }

    public function forgot(): View
    {
        return view('auth.forgot-password');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['login_password', 'login_otp'])],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'max:120'],
            'otp_code' => ['nullable', 'string', 'max:10'],
            'remember' => ['nullable'],
        ]);

        if ($validated['action'] === 'login_password') {
            $request->validate([
                'password' => ['required', 'string', 'max:120'],
            ]);

            if (! Auth::attempt(['phone' => $validated['phone'], 'password' => $validated['password']], $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'phone' => 'اطلاعات ورود صحیح نیست.',
                ]);
            }

            $request->session()->regenerate();

            return $this->redirectAfterAuth($request->user());
        }

        $request->validate([
            'otp_code' => ['required', 'string', 'max:10'],
        ]);

        $user = User::query()->where('phone', $validated['phone'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'کاربری با این شماره تلفن پیدا نشد.',
            ]);
        }

        $this->consumeOtpOrFail($validated['phone'], 'login', (string) $validated['otp_code']);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectAfterAuth($user);
    }

    public function authenticateAdmin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['login_password', 'login_otp'])],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'max:120'],
            'otp_code' => ['nullable', 'string', 'max:10'],
            'remember' => ['nullable'],
        ]);

        if ($validated['action'] === 'login_password') {
            $request->validate([
                'password' => ['required', 'string', 'max:120'],
            ]);

            if (! Auth::attempt(['phone' => $validated['phone'], 'password' => $validated['password']], $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'phone' => 'اطلاعات ورود صحیح نیست.',
                ]);
            }

            $request->session()->regenerate();

            $user = $request->user();
            $this->ensureAdminOrFail($request, $user);

            return redirect()->route('admin.dashboard');
        }

        $request->validate([
            'otp_code' => ['required', 'string', 'max:10'],
        ]);

        $user = User::query()->where('phone', $validated['phone'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'کاربری با این شماره تلفن پیدا نشد.',
            ]);
        }

        $this->consumeOtpOrFail($validated['phone'], 'login', (string) $validated['otp_code']);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->ensureAdminOrFail($request, $user);

        return redirect()->route('admin.dashboard');
    }

    public function forgotStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'otp_code' => ['required', 'string', 'max:10'],
            'password' => ['required', 'string', 'min:6', 'max:120', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'max:120'],
        ]);

        $user = User::query()->where('phone', $validated['phone'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => 'کاربری با این شماره تلفن پیدا نشد.',
            ]);
        }

        $this->consumeOtpOrFail($validated['phone'], 'password_reset', (string) $validated['otp_code']);

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectAfterAuth($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
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

    private function ensureAdminOrFail(Request $request, ?User $user): void
    {
        if ($user && $user->hasRole('admin')) {
            return;
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'phone' => 'دسترسی ورود ادمین ندارید.',
        ]);
    }

    private function redirectAfterAuth(?User $user): RedirectResponse
    {
        if (! $user) {
            return redirect()->route('home');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('panel.dashboard');
    }
}
