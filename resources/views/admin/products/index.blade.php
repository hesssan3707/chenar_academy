@extends('layouts.admin')

@section('title', $title ?? 'محصولات')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'محصولات' }}</h1>
                    <p class="page-subtitle">مدیریت محصولات</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--primary" href="{{ route('admin.products.create') }}">ایجاد محصول</a>
                </div>
            </div>

            @php($products = $products ?? null)

            @if (! $products || $products->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز محصولی ثبت نشده است.</p>
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
                                <th>وضعیت</th>
                                <th>قیمت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td class="admin-nowrap">{{ $product->type }}</td>
                                    <td class="admin-min-w-240">{{ $product->title }}</td>
                                    <td class="admin-nowrap">{{ $product->slug }}</td>
                                    <td>{{ $product->status }}</td>
                                    <td class="admin-nowrap">{{ number_format((int) $product->base_price) }} {{ $product->currency }}</td>
                                    <td class="admin-nowrap">{{ $product->published_at ? $product->published_at->format('Y-m-d H:i') : '—' }}</td>
                                    <td class="admin-nowrap">
                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.products.edit', $product->id) }}">ویرایش</a>
                                        <form method="post" action="{{ route('admin.products.destroy', $product->id) }}" class="inline-form">
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
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
