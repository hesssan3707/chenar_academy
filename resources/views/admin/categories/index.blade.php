@extends('layouts.admin')

@section('title', $title ?? 'دسته‌بندی‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دسته‌بندی‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت دسته‌بندی‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.categories.create') }}">ایجاد دسته‌بندی</a>
                </div>
            </div>

            @php($categories = $categories ?? null)

            @if (! $categories || $categories->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز دسته‌بندی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>نوع</th>
                                <th>عنوان</th>
                                <th>اسلاگ</th>
                                <th>والد</th>
                                <th>فعال</th>
                                <th>ترتیب</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td class="admin-nowrap">{{ $category->type }}</td>
                                    <td class="admin-min-w-240">{{ $category->title }}</td>
                                    <td class="admin-nowrap">{{ $category->slug }}</td>
                                    <td>{{ $category->parent_id ?: '—' }}</td>
                                    <td>{{ $category->is_active ? 'بله' : 'خیر' }}</td>
                                    <td>{{ $category->sort_order }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.categories.edit', $category->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.categories.destroy', $category->id) }}" class="inline-form">
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
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
