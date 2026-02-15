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
            </div>

            @php($products = $products ?? null)
            @php($currencyCode = strtoupper((string) ($commerceCurrency ?? 'IRR')))
            @php($currencyUnit = $currencyCode === 'IRT' ? 'تومان' : 'ریال')
            @php($activeCategoryType = (string) ($activeCategoryType ?? ''))
            @php($categoryOptions = $categoryOptions ?? collect())
            @php($typeLabels = [
                'course' => 'دوره',
                'video' => 'ویدیو',
                'note' => 'جزوه',
            ])

            @if (! $products || $products->isEmpty())
                <div class="panel max-w-md">
                    <p class="page-subtitle">هنوز محصولی ثبت نشده است.</p>
                </div>
            @else
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>نوع</th>
                                <th>عنوان</th>
                                <th>وضعیت</th>
                                <th>قیمت</th>
                                <th>انتشار</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                                <tr>
                                    @php($typeValue = (string) ($product->type ?? ''))
                                    <td class="admin-nowrap">{{ $typeLabels[$typeValue] ?? ($typeValue !== '' ? $typeValue : '—') }}</td>
                                    <td class="admin-min-w-240">{{ $product->title }}</td>
                                    <td class="admin-nowrap">
                                        @php($statusValue = (string) ($product->status ?? ''))
                                        @if ($statusValue === 'published')
                                            <span class="badge badge--brand">منتشر شده</span>
                                        @elseif ($statusValue === 'draft')
                                            <span class="badge">پیش‌نویس</span>
                                        @else
                                            <span class="badge">{{ $statusValue !== '' ? $statusValue : '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">
                                        @php($hasDiscount = (bool) ($product?->hasDiscount() ?? false))
                                        @php($discountType = (string) ($product->discount_type ?? ''))
                                        @php($discountValue = (int) ($product->discount_value ?? 0))
                                        @php($discountAmount = max(0, (int) $product->displayOriginalPrice($currencyCode) - (int) $product->displayFinalPrice($currencyCode)))
                                        <span class="money">
                                            <span class="money__amount" dir="ltr">{{ number_format((int) $product->displayOriginalPrice($currencyCode)) }}</span>
                                            <span class="money__unit">{{ $currencyUnit }}</span>
                                        </span>
                                        @if ($hasDiscount)
                                            @if ($discountType === 'percent' && $discountValue > 0)
                                                <span class="badge badge--danger" style="margin-inline-start: 8px;">{{ max(0, min(100, $discountValue)) }}%</span>
                                            @else
                                                <span class="badge badge--danger" style="margin-inline-start: 8px;">
                                                    <span class="money">
                                                        <span class="money__amount" dir="ltr">{{ number_format($discountAmount) }}</span>
                                                        <span class="money__unit">{{ $currencyUnit }}</span>
                                                    </span>
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="admin-nowrap">{{ $product->published_at ? jdate($product->published_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td>
                                        <div class="admin-row-actions">
                                            @if ($activeCategoryType !== '' && in_array($activeCategoryType, ['note', 'video', 'course'], true) && $categoryOptions->isNotEmpty())
                                                @php($currentCategoryId = (int) (($product->categories?->firstWhere('type', $activeCategoryType)?->id) ?? 0))
                                                <form method="post" action="{{ route('admin.products.category.update', $product->id) }}" class="inline-form">
                                                    @csrf
                                                    @method('put')
                                                    <select name="category_id" required>
                                                        @foreach ($categoryOptions as $categoryOption)
                                                            <option value="{{ $categoryOption->id }}" @selected((int) $categoryOption->id === $currentCategoryId)>
                                                                {{ $categoryOption->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button class="btn btn--ghost btn--sm" type="submit">تغییر</button>
                                                </form>
                                            @endif
                                            <form method="post" action="{{ route('admin.products.destroy', $product->id) }}" class="inline-form" data-confirm="1">
                                                @csrf
                                                @method('delete')
                                                <button class="btn btn--ghost btn--sm" type="submit">حذف</button>
                                            </form>
                                        </div>
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
