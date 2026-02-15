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
            @php($permissions = $permissions ?? collect())
            @php($selectedPermissionIds = collect(old('permission_ids', $selectedPermissionIds ?? []))->map(fn ($v) => (string) $v)->all())

            <div class="panel max-w-md">
                <form method="post"
                    action="{{ $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store') }}"
                    class="stack stack--sm"
                    id="role-form">
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

                    @if ($permissions->isNotEmpty())
                        <div class="divider"></div>

                        <div class="stack stack--xs">
                            <div class="admin-section-title">دسترسی‌ها</div>
                            <div class="page-subtitle admin-section-subtitle">دسترسی‌های این نقش</div>

                            <div class="stack stack--xs">
                                @foreach ($permissions as $permission)
                                    <label class="cluster">
                                        <input type="checkbox" name="permission_ids[]"
                                            value="{{ $permission->id }}" @checked(in_array((string) $permission->id, $selectedPermissionIds, true))>
                                        <span>{{ $permission->description ? ($permission->description . ' — ' . $permission->name) : $permission->name }}</span>
                                    </label>
                                @endforeach
                            </div>

                            @error('permission_ids')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                            @error('permission_ids.*')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </form>

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit" form="role-form">ذخیره</button>
                    @if ($isEdit)
                        <button class="btn btn--danger" type="submit" form="role-delete-form">حذف نقش</button>
                    @endif
                </div>

                @if ($isEdit)
                    <form method="post"
                        action="{{ route('admin.roles.destroy', $role->id) }}"
                        id="role-delete-form"
                        data-confirm="1"
                        data-confirm-title="حذف نقش"
                        data-confirm-message="آیا از حذف این نقش مطمئن هستید؟ این عملیات قابل بازگشت نیست.">
                        @csrf
                        @method('delete')
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
