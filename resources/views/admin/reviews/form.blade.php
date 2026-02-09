@extends('layouts.admin')

@section('title', $title ?? 'ویرایش نظر')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'ویرایش نظر' }}</h1>
                    <p class="page-subtitle">بررسی و مدیریت نظر کاربر</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.reviews.index') }}">بازگشت</a>
                </div>
            </div>

            @php($review = $review ?? null)

            <div class="panel">
                <div class="stack stack--sm">
                    <div class="card__meta">
                        <div>محصول: {{ $review?->product?->title ?? '—' }}</div>
                        <div>کاربر: {{ $review?->user?->name ?? 'کاربر' }} ({{ $review?->user?->phone ?? '—' }})</div>
                    </div>

                    <form method="post" action="{{ route('admin.reviews.update', $review->id) }}" class="stack stack--sm">
                        @csrf
                        @method('put')

                        <div class="grid admin-grid-2 admin-grid-2--flush">
                            <label class="field">
                                <span class="field__label">امتیاز</span>
                                <select name="rating" required>
                                    @for ($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected((int) old('rating', (int) ($review->rating ?? 5)) === $i)>{{ $i }}</option>
                                    @endfor
                                </select>
                                @error('rating')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">وضعیت</span>
                                @php($statusValue = (string) old('status', (string) ($review->status ?? 'approved')))
                                <select name="status" required>
                                    <option value="pending" @selected($statusValue === 'pending')>در انتظار</option>
                                    <option value="approved" @selected($statusValue === 'approved')>تایید شده</option>
                                    <option value="rejected" @selected($statusValue === 'rejected')>رد شده</option>
                                </select>
                                @error('status')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>
                        </div>

                        <label class="field">
                            <span class="field__label">متن نظر</span>
                            <textarea name="body" rows="6">{{ old('body', (string) ($review->body ?? '')) }}</textarea>
                            @error('body')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ذخیره</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

