@extends('layouts.admin')

@section('title', $title ?? 'ویدیوها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'ویدیوها' }}</h1>
                    <p class="page-subtitle">مدیریت ویدیوها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.videos.create') }}">ایجاد ویدیو</a>
                </div>
            </div>

            @php($videos = $videos ?? null)

            @if (! $videos || $videos->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز ویدیویی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>وضعیت</th>
                                <th>قیمت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($videos as $video)
                                <tr>
                                    <td class="admin-min-w-220">
                                        <div class="admin-row-title--sm">{{ $video->title }}</div>
                                        <div class="card__meta">{{ $video->slug }}</div>
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($statusValue = (string) ($video->status ?? ''))
                                        @if ($statusValue === 'published')
                                            <span class="badge badge--brand">منتشر شده</span>
                                        @elseif ($statusValue === 'draft')
                                            <span class="badge">پیش‌نویس</span>
                                        @else
                                            <span class="badge">{{ $statusValue !== '' ? $statusValue : '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($price = (int) ($video->sale_price ?? $video->base_price ?? 0))
                                        {{ number_format($price) }} {{ $video->currency ?? 'IRR' }}
                                    </td>
                                    <td class="admin-nowrap">
                                        {{ $video->published_at ? jdate($video->published_at)->format('Y/m/d H:i') : '—' }}
                                    </td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.videos.edit', $video->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $videos->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
