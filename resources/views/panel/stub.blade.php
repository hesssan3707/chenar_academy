@extends('layouts.app')

@section('title', $title ?? 'پنل کاربری')

@section('content')
    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'پنل کاربری' }}</h1>
            <p class="page-subtitle">این صفحه در مرحله فعلی به صورت استاب آماده شده است.</p>
        </div>
    </section>
@endsection
