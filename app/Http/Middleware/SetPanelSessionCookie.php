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

        return $next($request);
    }
}
