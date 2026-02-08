@extends('layouts.admin')

@section('title', $title ?? 'دسترسی')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دسترسی' }}</h1>
                    <p class="page-subtitle">اطلاعات دسترسی را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.permissions.index') }}">بازگشت</a>
                </div>
            </div>

            @php($permission = $permission ?? null)
            @php($isEdit = $permission && $permission->exists)

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.permissions.update', $permission->id) : route('admin.permissions.store') }}"
                    class="stack stack--sm">
                    @csrf
                    @if ($isEdit)
                        @method('put')
                    @endif

                    <label class="field">
                        <span class="field__label">نام</span>
                        <input name="name" required value="{{ old('name', (string) ($permission->name ?? '')) }}">
                        @error('name')
                            <div class="field__error">{{ $message }}</div>
                        @enderror
                    </label>

                    <label class="field">
                        <span class="field__label">توضیحات</span>
                        <input name="description" value="{{ old('description', (string) ($permission->description ?? '')) }}">
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
                    <form method="post" action="{{ route('admin.permissions.destroy', $permission->id) }}">
                        @csrf
                        @method('delete')
                        <button class="btn btn--ghost" type="submit">حذف دسترسی</button>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
