@extends('layouts.spa')

@section('title', $title ?? 'جزئیات سفارش')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')

            <main class="user-content panel-main flex flex-col" data-panel-main>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="h2 mb-1">{{ $title ?? 'جزئیات سفارش' }}</h2>
                        <p class="text-muted">شماره سفارش: {{ $order->order_number }}</p>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('panel.orders.index') }}">بازگشت</a>
                </div>

                <div class="stack stack--md">
                    <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl">
                        <div class="stack stack--sm">
                            @php($currencyUnit = (($order->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($order->currency ?? 'IRR'))
                            @php($statusLabel = match ((string) ($order->status ?? '')) {
                                'pending_review' => 'در انتظار تایید',
                                'rejected' => 'رد شده',
                                'paid' => 'تایید شده',
                                'cancelled' => 'لغو شده',
                                default => (string) ($order->status ?? ''),
                            })
                            <div class="flex justify-between">
                                <div class="stack stack--xs">
                                    <div class="text-muted">وضعیت</div>
                                    <div>{{ $statusLabel }}</div>
                                </div>
                                <div class="stack stack--xs text-left" dir="ltr">
                                    <div class="text-muted">مبلغ قابل پرداخت</div>
                                    <div class="card__price">
                                        <span class="price">{{ number_format((int) $order->payable_amount) }}</span>
                                        <span class="price__unit">{{ $currencyUnit }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <div class="stack stack--xs">
                                    <div class="text-muted">تاریخ ثبت</div>
                                    <div>{{ $order->placed_at ? jdate($order->placed_at)->format('Y/m/d H:i') : '—' }}</div>
                                </div>
                                <div class="stack stack--xs text-left" dir="ltr">
                                    <div class="text-muted">تاریخ پرداخت</div>
                                    <div>{{ $order->paid_at ? jdate($order->paid_at)->format('Y/m/d H:i') : '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl">
                        <div class="stack stack--sm">
                            <div class="text-muted">آیتم‌ها</div>
                            @foreach ($order->items as $item)
                                <div class="panel p-4 bg-white/5 border border-white/10 rounded-xl">
                                    <div class="flex justify-between items-start">
                                        <div class="stack stack--xs">
                                            <div class="font-bold">{{ $item->product_title }}</div>
                                            <div class="card__meta">نوع: {{ $item->product_type }} • تعداد: {{ (int) $item->quantity }}</div>
                                        </div>
                                        <div class="stack stack--xs text-left" dir="ltr">
                                            <div class="card__price">
                                                <span class="price">{{ number_format((int) $item->total_price) }}</span>
                                                <span class="price__unit">{{ $currencyUnit }}</span>
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
                </div>
            </main>
        </div>
    </div>
@endsection
