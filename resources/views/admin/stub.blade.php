@extends('layouts.admin')

@section('title', $title ?? 'مدیریت')

@section('content')
    <section class="section">
        <div class="container">
            <div class="admin-page-header">
                <div class="admin-page-header__titles">
                    <h1 class="page-title">{{ $title ?? 'مدیریت' }}</h1>
                    <p class="page-subtitle">این صفحه در مرحله فعلی به صورت استاب آماده شده است.</p>
                </div>
            </div>
        </div>
    </section>
@endsection
