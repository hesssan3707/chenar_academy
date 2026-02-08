@extends('layouts.admin')

@section('title', $title ?? 'بنرها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'بنرها' }}</h1>
                    <p class="page-subtitle">مدیریت بنرها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.banners.create') }}">ایجاد بنر</a>
                </div>
            </div>

            @php($banners = $banners ?? null)

            @if (! $banners || $banners->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز بنری ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>جایگاه</th>
                                <th>عنوان</th>
                                <th>فعال</th>
                                <th>ترتیب</th>
                                <th>شروع</th>
                                <th>پایان</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($banners as $banner)
                                <tr>
                                    <td>{{ $banner->id }}</td>
                                    <td class="admin-nowrap">{{ $banner->position }}</td>
                                    <td class="admin-min-w-240">{{ $banner->title ?? '—' }}</td>
                                    <td>{{ $banner->is_active ? 'بله' : 'خیر' }}</td>
                                    <td>{{ $banner->sort_order ?? 0 }}</td>
                                    <td class="admin-nowrap">{{ $banner->starts_at ? $banner->starts_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">{{ $banner->ends_at ? $banner->ends_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.banners.edit', $banner->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.banners.destroy', $banner->id) }}" class="inline-form">
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
                    {{ $banners->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
