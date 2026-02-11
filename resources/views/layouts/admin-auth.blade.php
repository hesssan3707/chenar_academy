<!doctype html>
<html lang="fa" dir="rtl" data-theme="admin">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="otp-send-url" content="{{ route('admin.otp.send') }}">

        <title>@yield('title', config('app.name'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    </head>
    <body>
        <main>
            @yield('content')
        </main>

        <div class="toast-host" data-toast-host></div>

        <script type="application/json" data-app-config>
            @json(['base_url' => url('/'), 'routes' => ['otp_send' => route('admin.otp.send')]])
        </script>

        <script type="application/json" data-flashes>
            @json(['toast' => session('toast'), 'otp_sent' => session('otp_sent')])
        </script>
    </body>
</html>
