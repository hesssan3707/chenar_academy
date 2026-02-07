@extends('layouts.app')

@section('title', 'پروفایل')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">پروفایل</h1>
            <p class="page-subtitle">مشخصات حساب کاربری</p>

            <div class="stack stack--sm max-w-md">
                <div class="panel">
                    <div class="stack stack--sm">
                        <div>
                            <span class="field__label">نام و نام خانوادگی</span>
                            <div>{{ trim((auth()->user()->first_name ?? '').' '.(auth()->user()->last_name ?? '')) ?: (auth()->user()->name ?? '-') }}</div>
                        </div>

                        <div>
                            <span class="field__label">شماره موبایل</span>
                            <div>{{ auth()->user()->phone }}</div>
                        </div>

                        <div>
                            <span class="field__label">ایمیل</span>
                            <div>{{ auth()->user()->email ?? '-' }}</div>
                        </div>

                        <div>
                            <span class="field__label">وضعیت</span>
                            <div>{{ auth()->user()->is_active ? 'فعال' : 'غیرفعال' }}</div>
                        </div>

                        <div class="form-actions">
                            <a class="btn btn--primary" href="{{ route('panel.dashboard') }}">رفتن به پنل</a>
                            <a class="btn btn--ghost" href="{{ route('home') }}">بازگشت به خانه</a>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <h2 class="section__title">تغییر رمز عبور</h2>

                    <form method="post" action="{{ route('panel.profile.password.update') }}" class="stack stack--sm">
                        @csrf
                        @method('put')

                        <label class="field">
                            <span class="field__label">رمز عبور فعلی</span>
                            <div class="input-group">
                                <input name="current_password" type="password" autocomplete="current-password">
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
                            @error('current_password')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">رمز عبور جدید</span>
                            <div class="input-group">
                                <input name="password" type="password" autocomplete="new-password">
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
                                <input name="password_confirmation" type="password" autocomplete="new-password">
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
                            <button class="btn btn--primary" type="submit">ثبت تغییرات</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
