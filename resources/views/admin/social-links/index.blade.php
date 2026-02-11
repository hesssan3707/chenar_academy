@extends('layouts.admin')

@section('title', $title ?? 'شبکه‌های اجتماعی')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'شبکه‌های اجتماعی' }}</h1>
                    <p class="page-subtitle">مدیریت لینک‌های شبکه‌های اجتماعی</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.social-links.create') }}">ایجاد لینک</a>
                </div>
            </div>

            @php($socialLinks = $socialLinks ?? null)

            @if (! $socialLinks || $socialLinks->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز لینکی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>پلتفرم</th>
                                <th>عنوان</th>
                                <th>آدرس</th>
                                <th>فعال</th>
                                <th>ترتیب</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($socialLinks as $socialLink)
                                <tr>
                                    <td>{{ $socialLink->id }}</td>
                                    <td class="admin-nowrap">{{ $socialLink->platform }}</td>
                                    <td class="admin-min-w-240">{{ $socialLink->title ?? '—' }}</td>
                                    <td class="admin-min-w-240">{{ $socialLink->url }}</td>
                                    <td>{{ $socialLink->is_active ? 'بله' : 'خیر' }}</td>
                                    <td>{{ $socialLink->sort_order ?? 0 }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.social-links.edit', $socialLink->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.social-links.destroy', $socialLink->id) }}" class="inline-form" data-confirm="1">
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
                    {{ $socialLinks->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
