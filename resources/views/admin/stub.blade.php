@extends('layouts.app')

@section('title', $title ?? 'مدیریت')

@section('content')
    @include('admin.partials.nav')

    <section class="section">
        <div class="container">
            <h1 class="page-title">{{ $title ?? 'مدیریت' }}</h1>
            <p class="page-subtitle">این صفحه در مرحله فعلی به صورت استاب آماده شده است.</p>
        </div>
    </section>
@endsection
