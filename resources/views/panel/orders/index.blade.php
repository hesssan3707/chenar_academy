@extends('layouts.app')

@section('title', $title ?? 'سفارش‌های من')

@section('content')
    @include('panel.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'سفارش‌های من' }}</h1>
            <p class="page-subtitle">لیست سفارش‌های ثبت‌شده</p>

            @if (($orders ?? collect())->isEmpty())
                <div class="panel max-w-md" style="margin-top: 18px;">
                    <p class="page-subtitle" style="margin: 0;">هنوز سفارشی ثبت نشده است.</p>
                </div>
            @else
                <div class="stack" style="margin-top: 18px;">
                    @foreach ($orders as $order)
                        @php($statusLabel = match ((string) ($order->status ?? '')) {
                            'pending_review' => 'در انتظار تایید',
                            'rejected' => 'رد شده',
                            'paid' => 'تایید شده',
                            'cancelled' => 'لغو شده',
                            default => (string) ($order->status ?? ''),
                        })
                        <a class="panel" href="{{ route('panel.orders.show', $order->id) }}" style="display:block;">
                            <div class="cluster" style="justify-content:space-between;align-items:flex-start;">
                                <div class="stack stack--sm">
                                    <div class="field__label">شماره سفارش: {{ $order->order_number }}</div>
                                    <div class="card__meta">
                                        وضعیت: {{ $statusLabel }}
                                        @if ($order->placed_at)
                                            • ثبت: {{ jdate($order->placed_at)->format('Y/m/d H:i') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="stack stack--sm" style="text-align:left;">
                                    <div class="card__price">
                                        @php($currencyUnit = (($order->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($order->currency ?? 'IRR'))
                                        <span class="price">{{ number_format((int) $order->payable_amount) }}</span>
                                        <span class="price__unit">{{ $currencyUnit }}</span>
                                    </div>
                                    <div class="card__meta">{{ ($order->items ?? collect())->count() }} آیتم</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
