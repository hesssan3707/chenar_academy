<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminUserScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $scopedUserId = $request->session()->get('admin_scoped_user_id');
        if (! is_int($scopedUserId)) {
            $scopedUserId = is_numeric($scopedUserId) ? (int) $scopedUserId : null;
        }

        $scopedUser = null;
        if (is_int($scopedUserId) && $scopedUserId > 0) {
            $scopedUser = User::query()->find($scopedUserId);
            if (! $scopedUser) {
                $request->session()->forget('admin_scoped_user_id');
                $scopedUserId = null;
            }
        } else {
            $scopedUserId = null;
        }

        $request->attributes->set('adminScopedUserId', $scopedUserId);
        $request->attributes->set('adminScopedUser', $scopedUser);

        View::share('adminScopedUser', $scopedUser);

        return $next($request);
    }
}
