@extends('layouts.spa')

@section('title', $title ?? 'سفارش‌های من')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main flex flex-col" data-panel-main>
                <h2 class="h2 mb-2">{{ $title ?? 'سفارش‌های من' }}</h2>
                <p class="text-muted mb-6">لیست سفارش‌های ثبت‌شده</p>

                @if (($orders ?? collect())->isEmpty())
                    <div class="panel p-6 bg-white/5 rounded-xl border border-gray-700">
                        <p class="text-muted">هنوز سفارشی ثبت نشده است.</p>
                    </div>
                @else
                    <div class="stack stack--md">
                        @foreach ($orders as $order)
                            @php($statusLabel = match ((string) ($order->status ?? '')) {
                                'pending_review' => 'در انتظار تایید',
                                'rejected' => 'رد شده',
                                'paid' => 'تایید شده',
                                'cancelled' => 'لغو شده',
                                default => (string) ($order->status ?? ''),
                            })
                            <a href="{{ route('panel.orders.show', $order->id) }}" class="panel p-4 block bg-white/5 border border-white/10 rounded-xl hover:bg-white/10 transition-colors">
                                <div class="cluster" style="justify-content:space-between;align-items:flex-start;">
                                    <div class="stack stack--xs">
                                        <div class="font-bold">شماره سفارش: <span dir="ltr">#{{ $order->order_number }}</span></div>
                                        <div class="text-sm text-muted">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs {{ $order->status === 'paid' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20' }}">
                                                {{ $statusLabel }}
                                            </span>
                                            @if ($order->placed_at)
                                                <span class="mx-2">•</span>
                                                {{ jdate($order->placed_at)->format('Y/m/d H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-left">
                                        @php($currencyUnit = (($order->currency ?? 'IRR') === 'IRR') ? 'تومان' : ($order->currency ?? 'IRR'))
                                        <div class="text-lg font-bold">
                                            {{ number_format((int) $order->payable_amount) }} <span class="text-sm font-normal text-muted">{{ $currencyUnit }}</span>
                                        </div>
                                        <div class="text-sm text-muted">{{ ($order->items ?? collect())->count() }} آیتم</div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection
