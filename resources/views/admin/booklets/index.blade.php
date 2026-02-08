@extends('layouts.admin')

@section('title', $title ?? 'جزوه‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'جزوه‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت جزوه‌ها</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.booklets.create') }}">ایجاد جزوه</a>
                </div>
            </div>

            @php($booklets = $booklets ?? null)

            @if (! $booklets || $booklets->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز جزوه‌ای ثبت نشده است.</p>
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
                            @foreach ($booklets as $booklet)
                                <tr>
                                    <td class="admin-min-w-220">
                                        <div class="admin-row-title--sm">{{ $booklet->title }}</div>
                                        <div class="card__meta">{{ $booklet->slug }}</div>
                                    </td>
                                    <td>{{ $booklet->status }}</td>
                                    <td class="admin-nowrap">
                                        @php($price = (int) ($booklet->sale_price ?? $booklet->base_price ?? 0))
                                        {{ number_format($price) }} {{ $booklet->currency ?? 'IRR' }}
                                    </td>
                                    <td class="admin-nowrap">
                                        {{ $booklet->published_at?->format('Y-m-d H:i') ?? '—' }}
                                    </td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.booklets.edit', $booklet->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $booklets->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
