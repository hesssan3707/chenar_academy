@extends('layouts.spa')

@section('title', 'محصولات')

@section('content')
    @php($products = $products ?? collect())
    @php($activeType = $activeType ?? 'all')
    @php($q = $q ?? '')

    <div class="spa-page" data-products-all>
        <div class="panel p-4 bg-white/5 border border-white/10 rounded-xl">
            <form method="get" action="{{ route('products.all') }}" class="stack stack--sm" data-products-all-form>
                <div class="cluster" style="justify-content: space-between; gap: 10px; padding: 0;">
                    <div class="cluster" style="gap: 10px; padding: 0;">
                        <button type="button" class="btn btn--sm {{ $activeType === 'all' ? 'btn--primary' : 'btn--ghost' }}" data-products-all-type="all">همه</button>
                        <button type="button" class="btn btn--sm {{ $activeType === 'note' ? 'btn--primary' : 'btn--ghost' }}" data-products-all-type="note">جزوه‌ها</button>
                        <button type="button" class="btn btn--sm {{ $activeType === 'video' ? 'btn--primary' : 'btn--ghost' }}" data-products-all-type="video">ویدیوها</button>
                    </div>

                    <div class="cluster" style="gap: 10px; padding: 0;">
                        <input type="hidden" name="type" value="{{ $activeType }}">
                        <input type="search" name="q" value="{{ $q }}" placeholder="جستجو..." style="width: min(520px, 60vw);" />
                        <button class="btn btn--primary" type="submit">جستجو</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="products-all-results" data-products-all-results>
            @include('catalog.products.partials.all_results', [
                'products' => $products,
                'purchasedProductIds' => $purchasedProductIds ?? [],
            ])
        </div>
    </div>
@endsection

