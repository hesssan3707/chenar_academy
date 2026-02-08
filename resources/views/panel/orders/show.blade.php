@extends('layouts.app')

@section('title', $title ?? 'جزئیات سفارش')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'جزئیات سفارش' }}</h1>
            <p class="page-subtitle">شماره سفارش: {{ $order->order_number }}</p>

            <div class="panel" style="margin-top: 18px;">
                <div class="stack stack--sm">
                    <div class="cluster" style="justify-content:space-between;">
                        <div class="stack stack--sm">
                            <div class="field__label">وضعیت</div>
                            <div>{{ $order->status }}</div>
                        </div>
                        <div class="stack stack--sm" style="text-align:left;">
                            <div class="field__label">مبلغ قابل پرداخت</div>
                            <div class="card__price">
                                <span class="price">{{ number_format((int) $order->payable_amount) }}</span>
                                <span class="price__unit">تومان</span>
                            </div>
                        </div>
                    </div>

                    <div class="cluster" style="justify-content:space-between;">
                        <div class="stack stack--sm">
                            <div class="field__label">تاریخ ثبت</div>
                            <div>{{ $order->placed_at ? jdate($order->placed_at)->format('Y/m/d H:i') : '—' }}</div>
                        </div>
                        <div class="stack stack--sm" style="text-align:left;">
                            <div class="field__label">تاریخ پرداخت</div>
                            <div>{{ $order->paid_at ? jdate($order->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel" style="margin-top: 18px;">
                <div class="stack stack--sm">
                    <div class="field__label">آیتم‌ها</div>
                    @foreach ($order->items as $item)
                        <div class="panel" style="background: rgba(15,26,46,0.35); border-style: dashed;">
                            <div class="cluster" style="justify-content:space-between;align-items:flex-start;">
                                <div class="stack stack--sm">
                                    <div class="field__label">{{ $item->product_title }}</div>
                                    <div class="card__meta">نوع: {{ $item->product_type }} • تعداد: {{ (int) $item->quantity }}</div>
                                </div>
                                <div class="stack stack--sm" style="text-align:left;">
                                    <div class="card__price">
                                        <span class="price">{{ number_format((int) $item->total_price) }}</span>
                                        <span class="price__unit">تومان</span>
                                    </div>
                                    @if ($item->product)
                                        <a class="btn btn--sm btn--ghost" href="{{ route('panel.library.show', $item->product->slug) }}">مشاهده در کتابخانه</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="form-actions" style="margin-top: 18px;">
                <a class="btn btn--ghost" href="{{ route('panel.orders.index') }}">بازگشت</a>
            </div>
        </div>
    </section>
@endsection
