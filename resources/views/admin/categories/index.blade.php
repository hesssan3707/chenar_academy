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
                @php($typeLabels = [
                    'institution' => 'دانشگاه',
                    'note' => 'جزوه',
                    'video' => 'ویدیو',
                    'course' => 'دوره',
                    'post' => 'مقاله',
                    'ticket' => 'تیکت',
                ])
                <div class="admin-grid-2 admin-categories-grid" style="margin-top: 18px;">
                    @foreach ($categoryGroups as $type => $nodes)
                        @if (! empty($nodes))
                            @php($relatedLabel = null)
                            @php($relatedRoute = null)
                            @if (in_array((string) $type, ['note', 'video', 'course'], true))
                                @php($relatedLabel = 'محصولات')
                                @php($relatedRoute = 'admin.products.index')
                            @elseif ((string) $type === 'post')
                                @php($relatedLabel = 'مقالات')
                                @php($relatedRoute = 'admin.posts.index')
                            @endif
                            <div class="panel">
                                <div class="stack stack--xs">
                                    <div class="field__label">نوع: {{ $typeLabels[$type] ?? $type }}</div>
                                </div>

                                <div class="divider"></div>

                                <div class="table-wrap">
                                    <table class="table table--sm table--compact table--fixed">
                                        <thead>
                                            <tr>
                                                <th>عنوان</th>
                                                <th>فعال</th>
                                                @if ($relatedLabel)
                                                    <th>{{ $relatedLabel }}</th>
                                                @endif
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($nodes as $node)
                                                @php($category = $node['category'])
                                                @php($relatedCount = (int) ($category->related_count ?? 0))
                                                @php($depth = (int) ($node['depth'] ?? 0))
                                                @php($indentPx = max(0, $depth) * 16)
                                                <tr>
                                                    <td class="admin-min-w-260">
                                                        <div class="admin-row-title" style="padding-inline-start: {{ $indentPx }}px;">{{ $category->title }}</div>
                                                    </td>
                                                    <td>{{ $category->is_active ? 'بله' : 'خیر' }}</td>
                                                    @if ($relatedLabel)
                                                        <td class="admin-nowrap">
                                                            @if ($relatedCount > 0)
                                                                <a href="{{ route($relatedRoute, ['category' => $category->id]) }}">{{ number_format($relatedCount) }}</a>
                                                            @else
                                                                0
                                                            @endif
                                                        </td>
                                                    @endif
                                                    <td class="admin-nowrap">
                                                        <a class="btn btn--ghost btn--sm" href="{{ route('admin.categories.edit', $category->id) }}">ویرایش</a>
                                                        @if (! in_array((string) $type, ['note', 'video', 'course'], true) || $relatedCount === 0)
                                                            <form method="post" action="{{ route('admin.categories.destroy', $category->id) }}" class="inline-form" data-confirm="1">
                                                                @csrf
                                                                @method('delete')
                                                                <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
