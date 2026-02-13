@extends('layouts.spa')

@section('title', 'بازیابی رمز عبور')

@section('content')
    <div class="container h-full flex items-center justify-center py-8">
        <div class="panel p-8 w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl backdrop-blur-md">
            <h1 class="h2 text-center mb-2">بازیابی رمز عبور</h1>
            <p class="text-center text-muted mb-6">ارسال کد تایید و تنظیم رمز عبور جدید</p>

            <form method="post" action="{{ route('password.forgot.store') }}" class="stack stack--sm">
                @csrf

                <label class="field">
                    <span class="field__label">شماره موبایل</span>
                    <input name="phone" type="tel" autocomplete="tel" dir="ltr" required value="{{ old('phone') }}">
                    @error('phone')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </label>

                <div class="field">
                    <span class="field__label">کد تایید</span>
                    <div class="input-group">
                        <input name="otp_code" type="text" inputmode="numeric" autocomplete="one-time-code" dir="ltr"
                            value="{{ old('otp_code') }}">
                        <button class="btn btn--sm" type="button" data-otp-send data-otp-purpose="password_reset">ارسال کد</button>
                    </div>
                    @error('otp_code')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                    <div class="field__error" data-otp-error hidden></div>
                </div>

                <label class="field">
                    <span class="field__label">رمز عبور جدید</span>
                    <div class="input-group">
                        <input name="password" type="password" autocomplete="new-password" dir="ltr">
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

                <label class="field">
                    <span class="field__label">تکرار رمز عبور جدید</span>
                    <div class="input-group">
                        <input name="password_confirmation" type="password" autocomplete="new-password" dir="ltr">
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
                    @error('password_confirmation')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </label>

                <div class="form-actions">
                    <button class="btn btn--primary w-full" type="submit">ثبت رمز جدید</button>
                    <a class="btn btn--ghost w-full" href="{{ route('login') }}">بازگشت</a>
                </div>
            </form>
        </div>
    </div>
@endsection
