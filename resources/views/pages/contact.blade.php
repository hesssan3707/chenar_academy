@extends('layouts.app')

@section('title', 'ارتباط با ما')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">ارتباط با ما</h1>
            <p class="page-subtitle">این صفحه در مرحله فعلی به صورت استاب آماده شده است.</p>

            <div class="panel max-w-md">
                <form method="post" action="{{ route('contact.submit') }}" class="stack stack--sm">
                    @csrf

                    <label class="field">
                        <span class="field__label">نام</span>
                        <input name="name" type="text" autocomplete="name" required>
                    </label>

                    <label class="field">
                        <span class="field__label">ایمیل</span>
                        <input name="email" type="email" autocomplete="email">
                    </label>

                    <label class="field">
                        <span class="field__label">پیام</span>
                        <textarea name="message" rows="5" required></textarea>
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ارسال پیام</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
