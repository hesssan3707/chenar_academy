<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPanelSessionCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        $baseCookieName = (string) config('session.cookie', 'laravel_session');

        if ($request->is('admin*')) {
            config(['session.cookie' => $baseCookieName.'_admin']);
            config(['auth.defaults.guard' => 'admin']);
        } else {
            config(['auth.defaults.guard' => 'web']);
        }

        $response = $next($request);

        if (! $request->is('admin*') && $request->hasSession()) {
            $countryCode = strtoupper(trim((string) $request->header('CF-IPCountry', '')));
            if (preg_match('/^[A-Z]{2}$/', $countryCode)) {
                $request->session()->put('analytics.country', $countryCode);
            }

            $userAgent = (string) $request->userAgent();
            $device = preg_match('/android|iphone|ipad|ipod|mobile|iemobile|opera mini|blackberry|webos/i', $userAgent) ? 'mobile' : 'web';
            $request->session()->put('analytics.device', $device);
        }

        return $response;
    }
}
