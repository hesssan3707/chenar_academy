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

            @php($categoryGroups = $categoryGroups ?? [])

            @if (empty($categoryGroups))
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز دسته‌بندی ثبت نشده است.</p>
                </div>
            @else
                @foreach ($categoryGroups as $type => $nodes)
                    @if (! empty($nodes))
                        <div class="panel" style="margin-top: 18px;">
                            <div class="stack stack--xs">
                                <div class="field__label">نوع: {{ $type }}</div>
                            </div>

                            <div class="divider"></div>

                            <div class="table-wrap">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>شناسه</th>
                                            <th>عنوان</th>
                                            <th>فعال</th>
                                            <th>ترتیب</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($nodes as $node)
                                            @php($category = $node['category'])
                                            @php($depth = (int) ($node['depth'] ?? 0))
                                            @php($indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', max(0, $depth)))
                                            <tr>
                                                <td>{{ $category->id }}</td>
                                                <td class="admin-min-w-260">
                                                    <div class="admin-row-title">{!! $indent !!}{{ $category->title }}</div>
                                                    <div class="card__meta">{!! $indent !!}{{ $category->slug }}</div>
                                                </td>
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
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </section>
@endsection
