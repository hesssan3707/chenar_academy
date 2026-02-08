@extends('layouts.app')

@section('title', $title ?? 'نظرسنجی')

@section('content')
    @include('admin.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'نظرسنجی' }}</h1>
            <p class="page-subtitle">سوال و گزینه‌های پاسخ را تنظیم کنید</p>

            @php($survey = $survey ?? null)
            @php($isEdit = $survey && $survey->exists)

            <div class="panel max-w-md">
                <form method="post" action="{{ $isEdit ? route('admin.surveys.update', $survey->id) : route('admin.surveys.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">سوال</span>
                        <textarea name="question" required>{{ old('question', (string) ($survey->question ?? '')) }}</textarea>
                        @error('question')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">گزینه‌ها (هر خط یک گزینه)</span>
                        @php($optionsRaw = old('options_raw', collect($survey->options ?? [])->filter(fn ($v) => is_string($v))->implode("\n")))
                        <textarea name="options_raw" required>{{ $optionsRaw }}</textarea>
                        @error('options_raw')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">مخاطب</span>
                        @php($audienceValue = (string) old('audience', (string) ($survey->audience ?? 'all')))
                        <select name="audience" required>
                            <option value="all" @selected($audienceValue === 'all')>همه کاربران</option>
                            <option value="authenticated" @selected($audienceValue === 'authenticated')>فقط کاربران واردشده</option>
                            <option value="purchasers" @selected($audienceValue === 'purchasers')>فقط خریداران</option>
                        </select>
                        @error('audience')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">شروع نمایش</span>
                        @php($startsAtValue = old('starts_at', $survey?->starts_at ? $survey->starts_at->format('Y-m-d\\TH:i') : ''))
                        <input type="datetime-local" name="starts_at" value="{{ $startsAtValue }}">
                        @error('starts_at')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">پایان نمایش</span>
                        @php($endsAtValue = old('ends_at', $survey?->ends_at ? $survey->ends_at->format('Y-m-d\\TH:i') : ''))
                        <input type="datetime-local" name="ends_at" value="{{ $endsAtValue }}">
                        @error('ends_at')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">وضعیت</span>
                        @php($activeValue = (string) old('is_active', ($survey?->is_active ?? true) ? '1' : '0'))
                        <select name="is_active">
                            <option value="1" @selected($activeValue === '1')>فعال</option>
                            <option value="0" @selected($activeValue === '0')>غیرفعال</option>
                        </select>
                        @error('is_active')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                        <a class="btn btn--ghost" href="{{ route('admin.surveys.index') }}">بازگشت</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

