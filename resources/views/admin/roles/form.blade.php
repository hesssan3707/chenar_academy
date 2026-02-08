@extends('layouts.admin')

@section('title', $title ?? 'نقش')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نقش' }}</h1>
                    <p class="page-subtitle">اطلاعات نقش را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.roles.index') }}">بازگشت</a>
                </div>
            </div>

            @php($role = $role ?? null)
            @php($isEdit = $role && $role->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">نام</span>
                        <input name="name" required value="{{ old('name', (string) ($role->name ?? '')) }}">
                        @error('name')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <input name="description" value="{{ old('description', (string) ($role->description ?? '')) }}">
                        @error('description')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit">ذخیره</button>
                    </div>
                </form>

                @if ($isEdit)
                    <div class="divider"></div>
                    <form method="post" action="{{ route('admin.roles.destroy', $role->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف نقش</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
