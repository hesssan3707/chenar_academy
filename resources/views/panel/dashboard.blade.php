@extends('layouts.app')

@section('title', 'پنل کاربری')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">پنل کاربری</h1>
            <p class="page-subtitle">دسترسی سریع به کتابخانه و پشتیبانی</p>

            <div class="grid grid--3" style="margin-top: 18px;">
                <a class="card" href="{{ route('panel.library.index') }}">
                    <div class="card__badge">کتابخانه</div>
                    <div class="card__title">مشاهده محتواهای خریداری‌شده</div>
                    <div class="card__meta">جزوه‌ها و ویدیوها</div>
                    <div class="card__action">ورود</div>
                </a>
                <a class="card" href="{{ route('panel.tickets.index') }}">
                    <div class="card__badge">پشتیبانی</div>
                    <div class="card__title">تیکت‌های من</div>
                    <div class="card__meta">ارسال پیام و پیگیری پاسخ‌ها</div>
                    <div class="card__action">مشاهده</div>
                </a>
            </div>
        </div>
    </section>
@endsection
