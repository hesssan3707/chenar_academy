<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUsersAccess
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasRole('super_admin'))) {
            abort(403);
        }

        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $permissionsEnabled = Schema::hasTable('permissions')
            && Schema::hasTable('role_permissions')
            && DB::table('role_permissions')->exists();
        if (! $permissionsEnabled) {
            return $next($request);
        }

        $permission = is_string($permission) && trim($permission) !== '' ? trim($permission) : 'admin.users';

        if (! $user->hasPermission($permission)) {
            abort(403);
        }

        return $next($request);
    }
}
