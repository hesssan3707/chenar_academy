@extends('layouts.spa')

@section('title', 'ثبت نام')

@section('content')
    <div class="container h-full flex items-center justify-center">
        <div class="panel p-8 w-full max-w-sm bg-white/5 border border-white/10 rounded-2xl backdrop-blur-md">
            <h1 class="h2 text-center mb-2">ثبت نام</h1>
            <p class="text-center text-muted mb-6">ایجاد حساب کاربری جدید</p>

            <form method="post" action="{{ route('register.store') }}" class="stack stack--sm">
                @csrf

                <div class="field">
                    <label class="field__label">نام و نام خانوادگی</label>
                    <input name="name" type="text" class="field__input" value="{{ old('name') }}">
                    @error('name') <div class="field__error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="field__label">شماره موبایل</label>
                    <input name="phone" type="tel" class="field__input" dir="ltr" value="{{ old('phone') }}">
                    @error('phone') <div class="field__error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label class="field__label">رمز عبور</label>
                    <input name="password" type="password" class="field__input" dir="ltr">
                    @error('password') <div class="field__error">{{ $message }}</div> @enderror
                </div>

                 <div class="field">
                    <label class="field__label">تکرار رمز عبور</label>
                    <input name="password_confirmation" type="password" class="field__input" dir="ltr">
                </div>

                <div class="field">
                    <label class="field__label">کد تایید</label>
                    <div class="input-group">
                        <input name="otp_code" type="text" class="field__input" dir="ltr">
                        <button class="btn btn--secondary btn--sm" type="button" data-otp-send data-otp-purpose="register">ارسال کد</button>
                    </div>
                     @error('otp_code') <div class="field__error">{{ $message }}</div> @enderror
                </div>

                <button class="btn btn--primary w-full mt-4" type="submit">ثبت نام</button>

                <div class="text-center mt-4 text-sm">
                    حساب دارید؟ <a href="#" onclick="openModal('auth-modal'); return false;">ورود</a>
                </div>
            </form>
        </div>
    </div>
@endsection
