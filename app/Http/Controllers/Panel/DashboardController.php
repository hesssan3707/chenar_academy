<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('panel.dashboard');
    }

    public function profile(): View
    {
        return view('panel.profile');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => $this->passwordPolicyRules(true),
            'password_confirmation' => ['required', 'string', 'max:120'],
        ];

        if ($user && is_string($user->password) && $user->password !== '') {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $request->user()->forceFill([
            'password' => $validated['password'],
        ])->save();

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'رمز عبور بروزرسانی شد',
            'message' => 'رمز عبور جدید با موفقیت ثبت شد.',
        ]);
    }
}
