@extends('layouts.admin-auth')

@section('title', 'ورود مدیر')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">ورود مدیر</h1>

            <div class="panel max-w-sm">
                <form method="post" action="{{ route('admin.login.store') }}" class="stack stack--sm">
                    @csrf

                    <input type="hidden" name="action" value="{{ old('action', 'login_password') }}" data-login-action>

                    <div class="cluster">
                        <button class="btn btn--primary btn--sm" type="button" data-login-mode="password">ورود با رمز</button>
                        <button class="btn btn--ghost btn--sm" type="button" data-login-mode="otp">ورود با کد</button>
                    </div>

                    <label class="field">
                        <span class="field__label">شماره موبایل</span>
                        <input name="phone" type="tel" autocomplete="tel" required value="{{ old('phone') }}">
                        @error('phone')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div data-login-section="password">
                        <label class="field">
                            <span class="field__label">رمز عبور</span>
                            <div class="input-group">
                                <input name="password" type="password" autocomplete="current-password">
                                <button class="btn btn--sm" type="button" data-password-toggle aria-label="نمایش رمز عبور">
                                    <svg data-password-icon="show" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <svg data-password-icon="hide" hidden xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.77 21.77 0 0 1 5.06-6.94"></path>
                                        <path d="M1 1l22 22"></path>
                                        <path d="M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 8 11 8a21.78 21.78 0 0 1-4.87 6.62"></path>
                                        <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>

                    <div class="field" data-login-section="otp" hidden>
                        <span class="field__label">کد یکبار مصرف</span>
                        <div class="input-group">
                            <input name="otp_code" type="text" inputmode="numeric" autocomplete="one-time-code" value="{{ old('otp_code') }}">
                            <button class="btn btn--sm" type="button" data-otp-send data-otp-purpose="admin_login">
                                ارسال کد
                            </button>
                        </div>
                        @error('otp_code')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                        <div class="field__error" data-otp-error hidden></div>
                    </div>

                    <label class="field">
                        <span class="field__label">مرا به خاطر بسپار</span>
                        <div class="cluster">
                            <input name="remember" type="checkbox" value="1" @checked(old('remember'))>
                            <span class="field__hint">در این دستگاه باقی بمانم</span>
                        </div>
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ورود به پنل مدیریت</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
