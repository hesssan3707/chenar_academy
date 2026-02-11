<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Http\JsonResponse;
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

    public function authenticate(Request $request): RedirectResponse|JsonResponse
    {
        $guard = Auth::guard('web');

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

            if (! $guard->attempt(['phone' => $validated['phone'], 'password' => $validated['password']], $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'phone' => 'اطلاعات ورود صحیح نیست.',
                ]);
            }

            $request->session()->regenerate();

            $redirect = redirect()->intended(route('panel.dashboard'));
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'redirect_to' => $redirect->getTargetUrl(),
                ]);
            }

            return $redirect;
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

        $guard->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $redirect = redirect()->intended(route('panel.dashboard'));
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'redirect_to' => $redirect->getTargetUrl(),
            ]);
        }

        return $redirect;
    }

    public function authenticateAdmin(Request $request): RedirectResponse|JsonResponse
    {
        $guard = Auth::guard('admin');

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

            if (! $guard->attempt(['phone' => $validated['phone'], 'password' => $validated['password']], $request->boolean('remember'))) {
                throw ValidationException::withMessages([
                    'phone' => 'اطلاعات ورود صحیح نیست.',
                ]);
            }

            $request->session()->regenerate();

            $user = $guard->user();
            $this->ensureAdminOrFail($request, $user, 'admin');

            $redirect = redirect()->intended(route('admin.dashboard'));
            if ($request->expectsJson()) {
                return response()->json([
                    'ok' => true,
                    'redirect_to' => $redirect->getTargetUrl(),
                ]);
            }

            return $redirect;
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

        $this->consumeOtpOrFail($validated['phone'], 'admin_login', (string) $validated['otp_code']);

        $guard->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->ensureAdminOrFail($request, $user, 'admin');

        $redirect = redirect()->intended(route('admin.dashboard'));
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'redirect_to' => $redirect->getTargetUrl(),
            ]);
        }

        return $redirect;
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

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('panel.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function logoutAdmin(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
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

    private function ensureAdminOrFail(Request $request, ?User $user, string $guard): void
    {
        if ($user && $user->hasRole('admin')) {
            return;
        }

        Auth::guard($guard)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        throw ValidationException::withMessages([
            'phone' => 'دسترسی ورود ادمین ندارید.',
        ]);
    }
}
