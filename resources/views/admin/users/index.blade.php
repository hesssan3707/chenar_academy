@extends('layouts.admin')

@section('title', $title ?? 'کاربران')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'کاربران' }}</h1>
                    <p class="page-subtitle">مدیریت کاربران ثبت‌نام‌شده</p>
                </div>
                <div class="admin-page-header__actions">
                    <form method="get" action="{{ route('admin.users.index') }}" class="admin-search">
                        <input type="search" name="q" placeholder="جستجوی نام یا موبایل" value="{{ request('q') }}">
                        <button class="btn btn--ghost" type="submit">جستجو</button>
                    </form>
                    <a class="btn btn--primary" href="{{ route('admin.users.create') }}">ایجاد کاربر</a>
                </div>
            </div>

            @php($users = $users ?? null)

            @if (($adminScopedUser ?? null))
                <div class="panel">
                    <div class="admin-page-header admin-page-header--flush">
                        <div class="page-subtitle">
                            پنل در حال حاضر بر اساس کاربر
                            <strong>{{ $adminScopedUser->name ?: $adminScopedUser->phone }}</strong>
                            فیلتر شده است.
                        </div>
                        <form method="post" action="{{ route('admin.scope.clear') }}">
                            @csrf
                            <button class="btn btn--ghost btn--sm" type="submit">حذف فیلتر</button>
                        </form>
                    </div>
                </div>
            @endif

            @if (! $users || $users->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز کاربری ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>نام</th>
                                <th>موبایل</th>
                                <th>فعال</th>
                                <th>تاریخ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td class="admin-min-w-200">{{ $user->name ?: '—' }}</td>
                                    <td class="admin-nowrap">{{ $user->phone ?: '—' }}</td>
                                    <td>{{ $user->is_active ? 'بله' : 'خیر' }}</td>
                                    <td class="admin-nowrap">{{ $user->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td class="admin-nowrap">
                                        @if (($adminScopedUser ?? null) && $adminScopedUser->id === $user->id)
                                            <span class="badge badge--brand">فعال</span>
                                        @else
                                            <form method="post" action="{{ route('admin.scope.user.store') }}" class="inline-form">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                <button class="btn btn--ghost btn--sm" type="submit">فیلتر</button>
                                            </form>
                                        @endif
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.users.edit', $user->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
