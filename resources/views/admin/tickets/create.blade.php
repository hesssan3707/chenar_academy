@extends('layouts.admin')

@section('title', $title ?? 'ایجاد تیکت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'ایجاد تیکت' }}</h1>
                    <p class="page-subtitle">تیکت جدید برای یک کاربر ایجاد کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.tickets.index') }}">بازگشت</a>
                </div>
            </div>

            <div class="panel max-w-md">
                <form method="post" action="{{ route('admin.tickets.store') }}" class="stack stack--sm">
                    @csrf

                    <label class="field">
                        <span class="field__label">شناسه کاربر</span>
                        @php($scopedUser = $adminScopedUser ?? null)
                        <input type="number" name="user_id" min="1" @if (! $scopedUser) required @endif
                            @if ($scopedUser) readonly @endif value="{{ old('user_id', $scopedUser?->id) }}">
                        @error('user_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">موضوع</span>
                        <input name="subject" required value="{{ old('subject') }}">
                        @error('subject')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">اولویت</span>
                        @php($priorityValue = (string) old('priority', 'normal'))
                        <select name="priority" required>
                            <option value="low" @selected($priorityValue === 'low')>کم</option>
                            <option value="normal" @selected($priorityValue === 'normal')>معمولی</option>
                            <option value="high" @selected($priorityValue === 'high')>بالا</option>
                        </select>
                        @error('priority')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">متن پیام</span>
                        <textarea name="body" required>{{ old('body') }}</textarea>
                        @error('body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ثبت</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
