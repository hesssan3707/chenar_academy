@extends('layouts.spa')

@section('title', 'ثبت نام')

@section('content')
    <div class="container h-full flex items-center justify-center py-8">
        <div class="panel p-8 w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl backdrop-blur-md">
            <h1 class="h2 text-center mb-2">ثبت نام</h1>
            <p class="text-center text-muted mb-6">ایجاد حساب کاربری جدید</p>

            <form method="post" action="{{ route('register.store') }}" class="stack stack--sm">
                @csrf

                <label class="field">
                    <span class="field__label">نام و نام خانوادگی</span>
                    <input name="name" type="text" value="{{ old('name') }}">
                    @error('name')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </label>

                <label class="field">
                    <span class="field__label">شماره موبایل</span>
                    <input name="phone" type="tel" dir="ltr" value="{{ old('phone') }}">
                    @error('phone')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </label>

                <label class="field">
                    <span class="field__label">رمز عبور</span>
                    <input name="password" type="password" dir="ltr">
                    @error('password')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                </label>

                <label class="field">
                    <span class="field__label">تکرار رمز عبور</span>
                    <input name="password_confirmation" type="password" dir="ltr">
                </label>

                <div class="field">
                    <span class="field__label">کد تایید</span>
                    <div class="input-group">
                        <input name="otp_code" type="text" dir="ltr" value="{{ old('otp_code') }}">
                        <button class="btn btn--secondary btn--sm" type="button" data-otp-send data-otp-purpose="register">ارسال کد</button>
                    </div>
                    @error('otp_code')
                        <div class="field__error">{{ $message }}</div>
                    @enderror
                    <div class="field__error" data-otp-error hidden></div>
                </div>

                <div class="form-actions">
                    <button class="btn btn--primary w-full" type="submit">ثبت نام</button>
                    <a class="btn btn--ghost w-full" href="{{ route('login') }}">ورود</a>
                </div>
            </form>
        </div>
    </div>
@endsection
