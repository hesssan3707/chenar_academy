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

        <!-- Goftino Widget Custom Positioning CSS -->
        <style>
            /* Desktop positioning - higher and more to the left */
            @media (min-width: 1025px) {
                #goftino,
                .goftino-widget,
                [data-goftino],
                iframe[src*="goftino"] {
                    bottom: 200px !important;
                    right: 70px !important;
                    position: fixed !important;
                    z-index: 9999 !important;
                    pointer-events: auto !important;
                }
            }

            /* Tablet positioning - adjust for tablet screens */
            @media (min-width: 768px) and (max-width: 1024px) {
                #goftino,
                .goftino-widget,
                [data-goftino],
                iframe[src*="goftino"] {
                    bottom: 220px !important;
                    right: 20px !important;
                    position: fixed !important;
                    z-index: 9999 !important;
                    pointer-events: auto !important;
                }
            }

            /* Mobile positioning - adjust position only, no sizing restrictions */
            @media (max-width: 767px) {
                #goftino,
                .goftino-widget,
                [data-goftino],
                iframe[src*="goftino"] {
                    bottom: 220px !important;
                    right: 15px !important;
                    position: fixed !important;
                    z-index: 9999 !important;
                    pointer-events: auto !important;
                    width: auto !important;
                    height: auto !important;
                    max-width: none !important;
                    max-height: none !important;
                    transform: none !important;
                }
            }

            /* Small phones - extra clearance from navigation */
            @media (max-width: 480px) {
                #goftino,
                .goftino-widget,
                [data-goftino],
                iframe[src*="goftino"] {
                    bottom: 200px !important;
                    right: 12px !important;
                }
            }
        </style>
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
                            <button class="btn btn--ghost" type="button" data-survey-optout>تمایل ندارم</button>
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

        <!-- Goftino Support Widget -->
        <script>
            !function(){var i="gnCnLa",d=document,g=d.createElement("script"),s="https://www.goftino.com/widget/"+i,l=localStorage.getItem("goftino_"+i);g.type="text/javascript",g.async=!0,g.src=l?s+"?o="+l:s;d.getElementsByTagName("head")[0].appendChild(g);}();
        </script>
    </body>
</html>
