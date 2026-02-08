@extends('layouts.admin')

@section('title', $title ?? 'نقش‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نقش‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت نقش‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.roles.create') }}">ایجاد نقش</a>
                </div>
            </div>

            @php($roles = $roles ?? null)

            @if (! $roles || $roles->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز نقشی ثبت نشده است.</p>
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
                            @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td class="admin-nowrap">{{ $role->name }}</td>
                                    <td class="admin-min-w-240">{{ $role->description ?? '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.roles.edit', $role->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.roles.destroy', $role->id) }}" class="inline-form">
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
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
