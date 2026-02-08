@extends('layouts.app')

@section('title', $title ?? 'ایجاد تیکت')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'ایجاد تیکت' }}</h1>
            <p class="page-subtitle">برای ارتباط با پشتیبانی، موضوع و متن پیام را وارد کنید</p>

            <div class="panel max-w-md" style="margin-top: 18px;">
                <form method="post" action="{{ route('panel.tickets.store') }}" class="stack stack--sm">
                    @csrf

                    @php($ticketCategories = $ticketCategories ?? collect())

                    <label class="field">
                        <span class="field__label">موضوع</span>
                        <input name="subject" required value="{{ old('subject') }}">
                        @error('subject')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">دسته‌بندی</span>
                        @php($categoryValue = (string) old('category', $ticketCategories->first()?->slug))
                        <select name="category" required>
                            @foreach ($ticketCategories as $category)
                                <option value="{{ $category->slug }}" @selected($categoryValue === $category->slug)>
                                    {{ $category->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">اولویت</span>
                        <select name="priority" required>
                            <option value="low" @selected(old('priority') === 'low')>کم</option>
                            <option value="normal" @selected(old('priority', 'normal') === 'normal')>معمولی</option>
                            <option value="high" @selected(old('priority') === 'high')>فوری</option>
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
                        <button class="btn btn--primary" type="submit">ثبت تیکت</button>
                        <a class="btn btn--ghost" href="{{ route('panel.tickets.index') }}">بازگشت</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
