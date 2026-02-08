@extends('layouts.admin')

@section('title', $title ?? 'دسترسی‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دسترسی‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت دسترسی‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.permissions.create') }}">ایجاد دسترسی</a>
                </div>
            </div>

            @php($permissions = $permissions ?? null)

            @if (! $permissions || $permissions->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز دسترسی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>نام</th>
                                <th>توضیحات</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td class="admin-nowrap">{{ $permission->name }}</td>
                                    <td class="admin-min-w-240">{{ $permission->description ?? '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.permissions.edit', $permission->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.permissions.destroy', $permission->id) }}" class="inline-form">
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

                <div class="admin-pagination">
                    {{ $permissions->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
