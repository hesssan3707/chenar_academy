<!doctype html>
<html lang="fa" dir="rtl" data-theme="{{ app('theme')->active() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">

        <title>@yield('title', config('app.name'))</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @include('partials.header')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')

        @if (($activeSurvey ?? null) && is_array($activeSurvey->options ?? null))
            <div class="modal" data-survey-modal hidden>
                <div class="modal__backdrop" data-survey-close></div>
                <div class="modal__dialog panel">
                    <div class="cluster" style="justify-content: space-between; align-items: flex-start;">
                        <div class="field__label">{{ $activeSurvey->question }}</div>
                        <button class="btn btn--ghost btn--sm" type="button" data-survey-close>بستن</button>
                    </div>

                    <form method="post" action="{{ route('surveys.responses.store', $activeSurvey->id) }}" class="stack stack--sm"
                        style="margin-top: 12px;">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ url()->current() }}">

                        <div class="stack stack--xs">
                            @foreach ($activeSurvey->options as $option)
                                @php($option = is_string($option) ? trim($option) : '')
                                @if ($option !== '')
                                    <label class="cluster" style="gap: 10px; align-items: center;">
                                        <input type="radio" name="answer" value="{{ $option }}" required>
                                        <span>{{ $option }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>

                        @error('answer')
                            <div class="field__error">{{ $message }}</div>
                        @enderror

                        <div class="form-actions" style="margin-top: 10px;">
                            <button class="btn btn--primary" type="submit">ثبت پاسخ</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="toast-host" data-toast-host></div>

        <script type="application/json" data-app-config>
            @json(['base_url' => url('/'), 'routes' => ['otp_send' => route('otp.send'), 'login' => route('login')]])
        </script>

        <script type="application/json" data-flashes>
            @json(['toast' => session('toast'), 'otp_sent' => session('otp_sent')])
        </script>
    </body>
</html>
