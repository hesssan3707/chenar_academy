@extends('layouts.admin')

@section('title', $title ?? 'دوره‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'دوره‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت دوره‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.courses.create') }}">ایجاد دوره</a>
                </div>
            </div>

            @php($courses = $courses ?? null)

            @if (! $courses || $courses->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز دوره‌ای ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>عنوان</th>
                                <th>اسلاگ</th>
                                <th>وضعیت</th>
                                <th>قیمت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courses as $course)
                                <tr>
                                    <td>{{ $course->id }}</td>
                                    <td class="admin-min-w-240">{{ $course->title }}</td>
                                    <td class="admin-nowrap">{{ $course->slug }}</td>
                                    <td>{{ $course->status }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) $course->base_price) }} {{ $course->currency }}</td>
                                    <td class="admin-nowrap">{{ $course->published_at ? jdate($course->published_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.courses.edit', $course->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.courses.destroy', $course->id) }}" class="inline-form">
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
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
