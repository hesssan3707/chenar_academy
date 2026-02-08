@extends('layouts.admin')

@section('title', $title ?? 'مقالات')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'مقالات' }}</h1>
                    <p class="page-subtitle">مدیریت مقالات وبلاگ</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.posts.create') }}">ایجاد مقاله</a>
                </div>
            </div>

            @php($posts = $posts ?? null)

            @if (! $posts || $posts->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز مقاله‌ای ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>وضعیت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($posts as $post)
                                <tr>
                                    <td class="admin-min-w-260">
                                        <div class="admin-row-title">{{ $post->title }}</div>
                                        <div class="card__meta">{{ $post->slug }}</div>
                                    </td>
                                    <td>{{ $post->status }}</td>
                                    <td class="admin-nowrap">{{ $post->published_at ? jdate($post->published_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.posts.edit', $post->id) }}">ویرایش</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="admin-pagination">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
