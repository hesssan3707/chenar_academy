@extends('layouts.admin')

@section('title', $title ?? 'رسانه‌ها')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'رسانه‌ها' }}</h1>
                    <p class="page-subtitle">مدیریت فایل‌های رسانه</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.media.create') }}">آپلود رسانه</a>
                </div>
            </div>

            @php($mediaItems = $mediaItems ?? null)

            @if (! $mediaItems || $mediaItems->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز رسانه‌ای ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>شناسه</th>
                                <th>Disk</th>
                                <th>Path</th>
                                <th>MIME</th>
                                <th>Size</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($mediaItems as $media)
                                <tr>
                                    <td>{{ $media->id }}</td>
                                    <td class="admin-nowrap">{{ $media->disk }}</td>
                                    <td class="admin-min-w-240">{{ $media->path }}</td>
                                    <td class="admin-nowrap">{{ $media->mime_type ?? '—' }}</td>
                                    <td class="admin-nowrap">{{ $media->size ? number_format((int) $media->size) : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.media.show', $media->id) }}">نمایش</a>
                                        <form method="post" action="{{ route('admin.media.destroy', $media->id) }}" class="inline-form" data-confirm="1">
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
                    {{ $mediaItems->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
