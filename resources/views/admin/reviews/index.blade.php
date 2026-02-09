@extends('layouts.admin')

@section('title', $title ?? 'نظرات')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'نظرات' }}</h1>
                    <p class="page-subtitle">مدیریت و بررسی نظرات کاربران</p>
                </div>
            </div>

            @php($reviews = $reviews ?? null)
            @php($activeStatus = $activeStatus ?? null)
            @php($approvalRequired = $approvalRequired ?? false)

            <div class="panel" style="margin-bottom: 18px;">
                <div class="cluster">
                    <div class="field__label">فیلتر وضعیت</div>
                    <a class="btn btn--sm {{ ! $activeStatus ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('admin.reviews.index') }}">همه</a>
                    <a class="btn btn--sm {{ $activeStatus === 'pending' ? 'btn--primary' : 'btn--ghost' }}"
                        href="{{ route('admin.reviews.index', ['status' => 'pending']) }}">در انتظار</a>
                    <a class="btn btn--sm {{ $activeStatus === 'approved' ? 'btn--primary' : 'btn--ghost' }}"
                        href="{{ route('admin.reviews.index', ['status' => 'approved']) }}">تایید شده</a>
                    <a class="btn btn--sm {{ $activeStatus === 'rejected' ? 'btn--primary' : 'btn--ghost' }}"
                        href="{{ route('admin.reviews.index', ['status' => 'rejected']) }}">رد شده</a>
                </div>
            </div>

            @if (! $reviews || $reviews->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز نظری ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>محصول</th>
                                <th>کاربر</th>
                                <th>امتیاز</th>
                                <th>نظر</th>
                                <th>وضعیت</th>
                                <th>ثبت</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviews as $review)
                                @php($status = (string) ($review->status ?? 'approved'))
                                @php($isNew = ! $approvalRequired && $status === 'approved' && $review->created_at && $review->created_at->greaterThanOrEqualTo(now()->subDay()))
                                <tr>
                                    <td class="admin-min-w-240">
                                        <div class="admin-row-title">{{ $review->product?->title ?? '—' }}</div>
                                        <div class="card__meta">{{ $review->product?->slug ?? '—' }}</div>
                                    </td>
                                    <td class="admin-min-w-240">
                                        <div class="admin-row-title">{{ $review->user?->name ?? 'کاربر' }}</div>
                                        <div class="card__meta">{{ $review->user?->phone ?? '—' }}</div>
                                    </td>
                                    <td>{{ (int) ($review->rating ?? 0) }}</td>
                                    <td class="admin-min-w-260">{{ $review->body ?? '—' }}</td>
                                    <td class="admin-nowrap">
                                        @if ($status === 'pending')
                                            <span class="badge badge--brand">در انتظار</span>
                                        @elseif ($status === 'rejected')
                                            <span class="badge">رد شده</span>
                                        @else
                                            <span class="badge">تایید شده</span>
                                        @endif
                                        @if ($isNew)
                                            <span class="badge badge--brand" style="margin-right: 8px;">جدید</span>
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">{{ $review->created_at ? jdate($review->created_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.reviews.edit', $review->id) }}">ویرایش</a>

                                        @if ($status === 'pending')
                                            <form method="post" action="{{ route('admin.reviews.approve', $review->id) }}" class="inline-form">
                                                @csrf
                                                <button class="btn btn--ghost btn--sm" type="submit">تایید</button>
                                            </form>
                                            <form method="post" action="{{ route('admin.reviews.reject', $review->id) }}" class="inline-form">
                                                @csrf
                                                <button class="btn btn--ghost btn--sm" type="submit">رد</button>
                                            </form>
                                        @endif

                                        <form method="post" action="{{ route('admin.reviews.destroy', $review->id) }}" class="inline-form">
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
                    {{ $reviews->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

