@extends('layouts.admin')

@section('title', $title ?? 'لینک شبکه اجتماعی')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'لینک شبکه اجتماعی' }}</h1>
                    <p class="page-subtitle">اطلاعات لینک را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.social-links.index') }}">بازگشت</a>
                </div>
            </div>

            @php($socialLink = $socialLink ?? null)
            @php($isEdit = $socialLink && $socialLink->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.social-links.update', $socialLink->id) : route('admin.social-links.store') }}"
                    class="stack stack--sm"
                    id="social-link-form">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">پلتفرم</span>
                        <input name="platform" required value="{{ old('platform', (string) ($socialLink->platform ?? '')) }}">
                        @error('platform')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">عنوان</span>
                        <input name="title" value="{{ old('title', (string) ($socialLink->title ?? '')) }}">
                        @error('title')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">آدرس</span>
                        <input name="url" required value="{{ old('url', (string) ($socialLink->url ?? '')) }}">
                        @error('url')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">Icon Media ID</span>
                        <input type="number" name="icon_media_id" min="1" max="2000000000"
                            value="{{ old('icon_media_id', (string) ($socialLink->icon_media_id ?? '')) }}">
                        @error('icon_media_id')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="grid admin-grid-2 admin-grid-2--flush">
                        <label class="field">
                            <span class="field__label">ترتیب</span>
                            <input type="number" name="sort_order" min="0" max="1000000"
                                value="{{ old('sort_order', (string) ($socialLink->sort_order ?? 0)) }}">
                            @error('sort_order')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            <label class="cluster">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $socialLink?->is_active ? '1' : '') === '1')>
                                <span>فعال</span>
                            </label>
                            @error('is_active')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>
                    </div>
                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="social-link-form">ذخیره</button>
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="social-link-delete-form">حذف لینک</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.social-links.destroy', $socialLink->id) }}"
                        id="social-link-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف لینک"
                        data-confirm-message="آیا از حذف این لینک مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
