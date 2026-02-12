@extends('layouts.spa')

@section('title', $title ?? 'پنل کاربری')

@section('content')
    <div class="spa-page-shell">
        <div class="user-panel-grid" data-panel-shell>
            @include('panel.partials.sidebar')
            
            <main class="user-content panel-main flex flex-col" data-panel-main>
                <div class="panel p-6 bg-white/5 border border-white/10 rounded-xl">
                    <h2 class="h2 mb-2">{{ $title ?? 'پنل کاربری' }}</h2>
                    <p class="text-muted">این صفحه در مرحله فعلی به صورت استاب آماده شده است.</p>
                </div>
            </main>
        </div>
    </div>
@endsection
