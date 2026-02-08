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
                </div>
            </div>

            @php($user = $user ?? null)
            @php($isEdit = $user && $user->exists)
            @php($isAdmin = (bool) ($isAdmin ?? false))

            <div class="stack">
                <div class="panel max-w-md">
                    <form method="post" action="{{ $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store') }}"
                        class="stack stack--sm">
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

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ذخیره</button>
                        </div>
                    </form>

                    @if ($isEdit)
                        <div class="divider"></div>
                        <form method="post" action="{{ route('admin.users.destroy', $user->id) }}">
                            @csrf
                            @method('delete')
                            <button class="btn btn--ghost" type="submit">حذف کاربر</button>
                        </form>
                    @endif
                </div>

                @if ($isEdit)
                    <div class="panel">
                        <div class="admin-page-header admin-page-header--flush">
                            <div class="admin-page-header__titles">
                                <div class="admin-section-title">دسترسی‌ها</div>
                                <p class="page-subtitle admin-section-subtitle">اعطای دسترسی محصول به کاربر</p>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="stack stack--sm">
                            <form method="get" action="{{ route('admin.users.edit', $user->id) }}" class="admin-search">
                                <input type="search" name="product_q" placeholder="جستجوی محصول" value="{{ $productQ ?? '' }}">
                                <button class="btn btn--ghost" type="submit">جستجو</button>
                            </form>

                        <form method="post" action="{{ route('admin.users.accesses.store', $user->id) }}" class="stack stack--sm">
                            @csrf

                            @php($products = $products ?? collect())
                            @php($accesses = $accesses ?? collect())

                            <label class="field">
                                <span class="field__label">محصول</span>
                                @php($productValue = (string) old('product_id', ''))
                                <select name="product_id" required>
                                    <option value="" @selected($productValue === '')>انتخاب کنید</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected($productValue === (string) $product->id)>
                                            {{ $product->title }} ({{ $product->type }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <label class="field">
                                <span class="field__label">تعداد روز اعتبار (اختیاری)</span>
                                <input type="number" name="expires_days" min="1" max="3650" value="{{ old('expires_days') }}">
                                @error('expires_days')
                                    <div class="field__error">{{ $message }}</div>
                                @enderror
                            </label>

                            <div class="form-actions">
                                <button class="btn btn--primary" type="submit">ثبت دسترسی</button>
                            </div>
                        </form>

                        @if ($accesses->isEmpty())
                            <div class="page-subtitle">هنوز دسترسی‌ای برای این کاربر ثبت نشده است.</div>
                        @else
                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>محصول</th>
                                            <th>اعطا شده</th>
                                            <th>انقضا</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($accesses as $access)
                                            <tr>
                                                <td class="admin-min-w-240">
                                                    {{ $access->product?->title ?: ('#' . $access->product_id) }}
                                                </td>
                                                <td class="admin-nowrap">
                                                    {{ $access->granted_at ? jdate($access->granted_at)->format('Y/m/d H:i') : '—' }}
                                                </td>
                                                <td class="admin-nowrap">
                                                    {{ $access->expires_at ? jdate($access->expires_at)->format('Y/m/d') : 'بدون انقضا' }}
                                                </td>
                                                <td class="admin-nowrap">
                                                    <form method="post"
                                                        action="{{ route('admin.users.accesses.destroy', [$user->id, $access->id]) }}"
                                                        class="inline-form">
                                                        @csrf
                                                        @method('delete')
                                                        <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            </div>
        </div>
    </section>
@endsection
