@extends('layouts.admin')

@section('title', $title ?? 'کاربر')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'کاربر' }}</h1>
                    <p class="page-subtitle">اطلاعات کاربر را تنظیم کنید</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.users.index') }}">بازگشت</a>
                    @if (($user ?? null) && $user->exists)
                        <a class="btn btn--ghost" href="{{ route('admin.users.products', $user->id) }}">محصولات</a>
                    @endif
                </div>
            </div>

            @php($user = $user ?? null)
            @php($isEdit = $user && $user->exists)
            @php($isAdmin = (bool) ($isAdmin ?? false))
            @php($roles = $roles ?? collect())
            @php($selectedRoleIds = collect(old('role_ids', $selectedRoleIds ?? []))->map(fn ($v) => (string) $v)->all())

            <div class="stack">
                <div class="panel max-w-md">
                    <form method="post" action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
                        class="stack stack--sm"
                        id="user-form">
                        @csrf
                        @if ($isEdit)
                            @method('put')
                        @endif

                        <label class="field">
                            <span class="field__label">نام</span>
                            <input name="name" required value="{{ old('name', (string) ($user->name ?? '')) }}">
                            @error('name')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">موبایل</span>
                            <input name="phone" required value="{{ old('phone', (string) ($user->phone ?? '')) }}">
                            @error('phone')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">رمز عبور {{ $isEdit ? '(اختیاری)' : '' }}</span>
                            <input name="password" type="password" {{ $isEdit ? '' : 'required' }}>
                            @error('password')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">فعال</span>
                            @php($isActiveValue = (string) old('is_active', ($user && $user->is_active) ? '1' : '0'))
                            <select name="is_active">
                                <option value="1" @selected($isActiveValue === '1')>بله</option>
                                <option value="0" @selected($isActiveValue === '0')>خیر</option>
                            </select>
                            @error('is_active')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <label class="field">
                            <span class="field__label">ادمین</span>
                            @php($isAdminValue = (string) old('is_admin', $isAdmin ? '1' : '0'))
                            <select name="is_admin">
                                <option value="0" @selected($isAdminValue === '0')>خیر</option>
                                <option value="1" @selected($isAdminValue === '1')>بله</option>
                            </select>
                            @error('is_admin')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        @php($selectableRoles = $roles->filter(fn ($role) => ! in_array((string) ($role->name ?? ''), ['admin', 'super_admin'], true)))
                        @if ($selectableRoles->isNotEmpty())
                            <div class="divider"></div>

                            <div class="stack stack--xs">
                                <div class="admin-section-title">نقش‌ها</div>
                                <div class="page-subtitle admin-section-subtitle">نقش‌های این کاربر (برای دسترسی‌های پنل مدیریت)</div>

                                <div class="stack stack--xs">
                                    @foreach ($selectableRoles as $role)
                                        <label class="cluster">
                                            <input type="checkbox" name="role_ids[]"
                                                value="{{ $role->id }}" @checked(in_array((string) $role->id, $selectedRoleIds, true))>
                                            <span>{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('role_ids')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                                @error('role_ids.*')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    </form>

                    <div class="form-actions">
                        <button class="btn btn--primary" type="submit" form="user-form">ذخیره</button>
                        @if ($isEdit)
                            <button class="btn btn--danger" type="submit" form="user-delete-form">حذف کاربر</button>
                        @endif
                    </div>

                    @if ($isEdit)
                        <form method="post"
                            action="{{ route('admin.users.destroy', $user->id) }}"
                            id="user-delete-form"
                            data-confirm="1"
                            data-confirm-title="حذف کاربر"
                            data-confirm-message="آیا از حذف این کاربر مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                            @csrf
                            @method('delete')
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
