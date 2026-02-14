@extends('layouts.spa')

@section('title', 'پنل کاربری')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main" data-panel-main>
                <h2 class="h2 mb-6">داشبورد</h2>
                
                <div class="grid grid--3 gap-4">
                    <a href="{{ route('panel.library.index') }}" class="stat-card">
                        <div class="stat-card__value">کتابخانه</div>
                        <div class="stat-card__label">دسترسی به محتواهای خریداری شده</div>
                    </a>
                    <a href="{{ route('panel.orders.index') }}" class="stat-card">
                        <div class="stat-card__value">سفارش‌ها</div>
                        <div class="stat-card__label">مشاهده وضعیت سفارش‌ها</div>
                    </a>
                    <a href="{{ route('panel.tickets.index') }}" class="stat-card">
                        <div class="stat-card__value">پشتیبانی</div>
                        <div class="stat-card__label">ارسال و پیگیری تیکت</div>
                    </a>
                </div>
                
                <div class="mt-8">
                    <h3 class="h3 mb-4">اطلاعات حساب</h3>
                    <div class="panel">
                        <div class="cluster" style="justify-content: space-between;">
                            <div>
                                <div class="text-sm text-muted">نام و نام خانوادگی</div>
                                <div>{{ auth()->user()->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-muted">شماره موبایل</div>
                                <div>{{ auth()->user()->mobile ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-muted">تاریخ عضویت</div>
                                <div dir="ltr">{{ auth()->user()->created_at->format('Y/m/d') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection
