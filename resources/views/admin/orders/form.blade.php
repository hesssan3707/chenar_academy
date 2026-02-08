@extends('layouts.admin')

@section('title', $title ?? 'سفارش')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'سفارش' }}</h1>
                    <p class="page-subtitle">تنظیم وضعیت سفارش</p>
                </div>
                <div class="admin-page-header__actions">
                    <a class="btn btn--ghost" href="{{ route('admin.orders.index') }}">بازگشت</a>
                    @if (($order ?? null) && $order->exists)
                        <a class="btn btn--ghost" href="{{ route('admin.orders.show', $order->id) }}">نمایش</a>
                    @endif
                </div>
            </div>

            @php($order = $order ?? null)
            @php($isEdit = $order && $order->exists)

            <div class="panel max-w-md">
                @if (! $isEdit)
                    <p class="page-subtitle">ثبت سفارش از طریق فرآیند پرداخت انجام می‌شود.</p>
                @else
                    <form method="post" action="{{ route('admin.orders.update', $order->id) }}" class="stack stack--sm">
                        @csrf
                        @method('put')

                        <label class="field">
                            <span class="field__label">وضعیت</span>
                            @php($statusValue = old('status', (string) ($order->status ?? 'pending')))
                            <select name="status" required>
                                <option value="pending" @selected($statusValue === 'pending')>pending</option>
                                <option value="paid" @selected($statusValue === 'paid')>paid</option>
                                <option value="cancelled" @selected($statusValue === 'cancelled')>cancelled</option>
                            </select>
                            @error('status')
                                <div class="field__error">{{ $message }}</div>
                            @enderror
                        </label>

                        <div class="form-actions">
                            <button class="btn btn--primary" type="submit">ذخیره</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
@endsection
