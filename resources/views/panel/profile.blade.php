@extends('layouts.spa')

@section('title', 'پروفایل')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main" data-panel-main>
                <h2 class="h2 mb-6">تنظیمات حساب</h2>
                
                <div class="stack stack--md">
                    <div class="panel p-6 border border-gray-700 bg-white/5 rounded-xl">
                        <h3 class="h3 mb-4">مشخصات</h3>
                        <div class="panel-account-grid">
                            <div class="panel panel--soft p-4">
                                <div class="text-sm text-muted mb-1">نام و نام خانوادگی</div>
                                <div class="text-lg font-bold">{{ auth()->user()->name ?? '-' }}</div>
                            </div>
                            <div class="panel panel--soft p-4" dir="ltr">
                                <div class="text-sm text-muted mb-1" dir="rtl">شماره موبایل</div>
                                <div class="text-lg font-bold">{{ auth()->user()->mobile ?? auth()->user()->phone ?? '-' }}</div>
                            </div>
                            <div class="panel panel--soft p-4" dir="ltr">
                                <div class="text-sm text-muted mb-1" dir="rtl">تاریخ عضویت</div>
                                <div class="text-lg font-bold">{{ auth()->user()?->created_at ? jdate(auth()->user()->created_at)->format('Y/m/d') : '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="panel p-6 border border-gray-700 bg-white/5 rounded-xl">
                        <h3 class="h3 mb-4">تغییر رمز عبور</h3>

                        <form method="post" action="{{ route('panel.profile.password.update') }}" class="stack stack--sm max-w-md">
                            @csrf
                            @method('put')

                            <div class="field">
                                <label class="field__label">رمز عبور فعلی</label>
                                <input name="current_password" type="password" class="field__input" dir="ltr">
                                @error('current_password')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field__label">رمز عبور جدید</label>
                                <input name="password" type="password" class="field__input" dir="ltr">
                                @error('password')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">
                                <label class="field__label">تکرار رمز عبور جدید</label>
                                <input name="password_confirmation" type="password" class="field__input" dir="ltr">
                            </div>

                            <div class="form-actions mt-4">
                                <button class="btn btn--primary" type="submit">ذخیره تغییرات</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
