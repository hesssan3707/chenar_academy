@extends('layouts.app')

@section('title', 'درگاه پرداخت تستی')

@section('content')
<div class="container py-5 my-5 text-center">
    <div class="card mx-auto shadow-sm" style="max-width: 500px;">
        <div class="card-body p-5">
            <h2 class="mb-4 text-primary">درگاه پرداخت تستی</h2>
            
            <div class="alert alert-info">
                این صفحه تنها در محیط توسعه نمایش داده می‌شود تا فرآیند پرداخت شبیه‌سازی شود.
            </div>

            <p class="fs-5 mb-2">مبلغ پرداختی:</p>
            <p class="fs-3 fw-bold text-success mb-5">{{ number_format($amount) }} {{ $commerceCurrencyLabel ?? 'تومان' }}</p>

            <form action="{{ route('fake-gateway.process') }}" method="POST">
                @csrf
                <input type="hidden" name="authority" value="{{ $authority }}">
                <input type="hidden" name="callback" value="{{ $callback }}">
                
                <div class="d-grid gap-3">
                    <button type="submit" name="status" value="OK" class="btn btn-success btn-lg">
                        پرداخت موفق
                    </button>
                    <button type="submit" name="status" value="NOK" class="btn btn-danger btn-lg">
                        انصراف از پرداخت
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
