@extends('layouts.app')

@section('title', 'محصولات')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">محصولات</h1>
            <p class="page-subtitle">لیست جزوه‌ها و ویدیوها</p>

            <div class="stack">
                <div class="cluster">
                    <a class="btn {{ $activeType ? 'btn--ghost' : 'btn--primary' }}" href="{{ route('products.index') }}">همه</a>
                    <a class="btn {{ $activeType === 'note' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', ['type' => 'note']) }}">جزوه‌ها</a>
                    <a class="btn {{ $activeType === 'video' ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', ['type' => 'video']) }}">ویدیوها</a>
                </div>

                @if ($activeType && in_array($activeType, ['note', 'video'], true) && ($institutions ?? collect())->isNotEmpty())
                    <div class="panel">
                        <div class="stack stack--sm">
                            <div class="cluster">
                                <div class="field__label">دسته‌بندی‌ها</div>
                                <a class="btn btn--sm {{ ! $activeInstitution && ! $activeCategory ? 'btn--primary' : 'btn--ghost' }}" href="{{ route('products.index', ['type' => $activeType]) }}">همه</a>
                                @if ($activeInstitution)
                                    <a class="btn btn--sm btn--ghost" href="{{ route('products.index', ['type' => $activeType]) }}">پاک کردن فیلتر</a>
                                @endif
                            </div>

                            @foreach ($institutions as $institution)
                                @php($childCategories = $institution->children ?? collect())
                                @continue($childCategories->isEmpty())

                                <div class="stack stack--sm">
                                    <div class="cluster" style="justify-content: space-between;">
                                        <div class="cluster">
                                            @if ($institution->icon_key)
                                                <span aria-hidden="true" style="display:inline-flex;align-items:center;color:rgba(232,238,252,0.8);">
                                                    @switch($institution->icon_key)
                                                        @case('university')
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                <path d="M3 10l9-5 9 5" />
                                                                <path d="M5 10v9" />
                                                                <path d="M19 10v9" />
                                                                <path d="M9 22V14h6v8" />
                                                                <path d="M3 19h18" />
                                                            </svg>
                                                            @break
                                                    @endswitch
                                                </span>
                                            @endif
                                            <div class="field__label">{{ $institution->title }}</div>
                                        </div>

                                        <a class="btn btn--sm {{ ($activeInstitution && $activeInstitution->id === $institution->id && ! $activeCategory) ? 'btn--primary' : 'btn--ghost' }}"
                                            href="{{ route('products.index', ['type' => $activeType, 'institution' => $institution->slug]) }}">نمایش</a>
                                    </div>

                                    <div class="cluster">
                                        @foreach ($childCategories as $category)
                                            <a class="btn btn--sm {{ ($activeCategory && $activeCategory->id === $category->id) ? 'btn--primary' : 'btn--ghost' }}"
                                                href="{{ route('products.index', ['type' => $activeType, 'institution' => $institution->slug, 'category' => $category->slug]) }}"
                                                style="display:inline-flex;align-items:center;gap:8px;">
                                                @if ($category->icon_key)
                                                    <span aria-hidden="true" style="display:inline-flex;align-items:center;">
                                                        @switch($category->icon_key)
                                                            @case('math')
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M4 7h16" />
                                                                    <path d="M7 4v6" />
                                                                    <path d="M17 4v6" />
                                                                    <path d="M6 12h4" />
                                                                    <path d="M8 10v4" />
                                                                    <path d="M14 10h4" />
                                                                    <path d="M14 14h4" />
                                                                    <path d="M6 18h4" />
                                                                    <path d="M14 18h4" />
                                                                </svg>
                                                                @break
                                                            @case('physics')
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M12 12m-1.5 0a1.5 1.5 0 1 0 3 0a1.5 1.5 0 1 0 -3 0" />
                                                                    <path d="M4.5 12c0-4.4 3.4-8 7.5-8s7.5 3.6 7.5 8-3.4 8-7.5 8-7.5-3.6-7.5-8" />
                                                                    <path d="M7.2 7.2c3.1-3.1 8.5-2.6 12.1 1.1" />
                                                                    <path d="M4.7 15.8c3.6 3.6 9 4.2 12.1 1.1" />
                                                                </svg>
                                                                @break
                                                            @case('calculator')
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <rect x="6" y="3" width="12" height="18" rx="2" />
                                                                    <path d="M8.5 7h7" />
                                                                    <path d="M9 11h2" />
                                                                    <path d="M13 11h2" />
                                                                    <path d="M9 14h2" />
                                                                    <path d="M13 14h2" />
                                                                    <path d="M9 17h2" />
                                                                    <path d="M13 17h2" />
                                                                </svg>
                                                                @break
                                                            @case('video')
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M4 7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z" />
                                                                    <path d="M16 10l4-2v8l-4-2z" />
                                                                </svg>
                                                                @break
                                                        @endswitch
                                                    </span>
                                                @endif
                                                <span>{{ $category->title }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid--3">
                    @foreach ($products as $product)
                        @php($purchased = in_array($product->id, ($purchasedProductIds ?? []), true))
                        <a class="card" href="{{ route('products.show', $product->slug) }}">
                            <div class="card__badge">{{ $product->type === 'video' ? 'ویدیو' : 'جزوه' }}@if ($purchased) • خریداری شده @endif</div>
                            <div class="card__title">{{ $product->title }}</div>
                            <div class="card__price">
                                <span class="price">{{ number_format($product->sale_price ?? $product->base_price) }}</span>
                                <span class="price__unit">تومان</span>
                            </div>
                            <div class="card__meta">{{ $product->excerpt ?? 'برای مشاهده جزئیات کلیک کنید' }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
