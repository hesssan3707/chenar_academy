@extends('layouts.spa')

@section('title', 'پروفایل')

@section('content')
    <div class="container h-full py-6">
        <div class="user-panel-grid">
            @include('panel.partials.sidebar')
            
            <main class="user-content">
                <h2 class="h2 mb-6">تنظیمات حساب</h2>
                
                <div class="stack stack--md">
                    <div class="panel p-6 border border-gray-700 bg-white/5 rounded-xl">
                        <h3 class="h3 mb-4">مشخصات</h3>
                        <div class="grid grid--2 gap-4">
                            <div>
                                <span class="text-sm text-muted block mb-1">نام و نام خانوادگی</span>
                                <div class="text-lg">{{ auth()->user()->name ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-muted block mb-1">شماره موبایل</span>
                                <div class="text-lg">{{ auth()->user()->mobile ?? auth()->user()->phone ?? '-' }}</div>
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
