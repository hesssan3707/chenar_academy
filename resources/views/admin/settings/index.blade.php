@extends('layouts.app')

@section('title', 'تنظیمات')

@section('content')
    @include('admin.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">تنظیمات</h1>
            <p class="page-subtitle">تنظیمات پایه سایت و قالب بصری</p>

            <div class="panel max-w-md">
                <form method="post" action="{{ route('admin.settings.update') }}" class="stack stack--sm">
                    @csrf
                    @method('put')

                    <label class="field">
                        <span class="field__label">قالب (Theme)</span>
                        <select name="theme" required>
                            @foreach ($themes as $theme)
                                <option value="{{ $theme }}" @selected($activeTheme === $theme)>{{ $theme }}</option>
                            @endforeach
                        </select>
                        @error('theme')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div style="height: 1px; background: var(--border); margin: 8px 0;"></div>

                    <div class="field__label">صفحه درباره ما</div>

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="about_title" value="{{ old('about_title', (string) ($about['title'] ?? '')) }}">
                        @error('about_title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">زیرعنوان</span>
                        <input name="about_subtitle" value="{{ old('about_subtitle', (string) ($about['subtitle'] ?? '')) }}">
                        @error('about_subtitle')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">متن</span>
                        <textarea name="about_body">{{ old('about_body', (string) ($about['body'] ?? '')) }}</textarea>
                        @error('about_body')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
