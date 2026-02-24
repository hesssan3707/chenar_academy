@extends('layouts.admin')

@section('title', $title ?? 'محصولات کاربر')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'محصولات کاربر' }}</h1>
                    @php($user = $user ?? null)
                    <p class="page-subtitle">
                        @if ($user)
                            مدیریت محصولات و دسترسی‌های {{ $user->name ?: 'کاربر' }}
                            @if ($user->phone)
                                <span class="text-muted" dir="ltr">{{ $user->phone }}</span>
                            @endif
                        @else
                            مدیریت محصولات و دسترسی‌های کاربر
                        @endif
                    </p>
                </div>
                <div class="admin-page-header__actions">
                    @if ($user)
                        <a class="btn btn--ghost" href="{{ route('admin.users.edit', $user->id) }}">ویرایش</a>
                    @endif
                    <a class="btn btn--ghost" href="{{ route('admin.users.index') }}">بازگشت</a>
                </div>
            </div>

            @if (! $user)
                <div class="panel max-w-md">
                    <p class="page-subtitle">کاربر یافت نشد.</p>
                </div>
            @else
                @php($products = $products ?? collect())
                @php($accesses = $accesses ?? collect())

                <div class="stack">
                    <div class="panel">
                        <div class="admin-page-header admin-page-header--flush">
                            <div class="admin-page-header__titles">
                                <div class="admin-section-title">محصولات</div>
                                <p class="page-subtitle admin-section-subtitle">اعطای دسترسی محصول به کاربر</p>
                            </div>
                        </div>

                        <div class="divider"></div>

                        <div class="stack stack--sm">
                            <form method="get" action="{{ route('admin.users.products', $user->id) }}" class="admin-search">
                                <input type="search" name="product_q" placeholder="جستجوی محصول" value="{{ $productQ ?? '' }}">
                                <button class="btn btn--ghost" type="submit">جستجو</button>
                            </form>

                            <form method="post" action="{{ route('admin.users.accesses.store', $user->id) }}" class="stack stack--sm">
                                @csrf

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
                                    <button class="btn btn--primary" type="submit">افزودن محصول</button>
                                </div>
                            </form>

                            @if ($accesses->isEmpty())
                                <div class="page-subtitle">هنوز محصولی برای این کاربر ثبت نشده است.</div>
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
                                                            class="inline-form"
                                                            data-confirm="1"
                                                            data-confirm-title="حذف محصول"
                                                            data-confirm-message="آیا از حذف دسترسی این محصول مطمئن هستید؟">
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
                </div>
            @endif
        </div>
    </section>
@endsection
