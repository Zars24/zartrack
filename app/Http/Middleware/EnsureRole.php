<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, 'Akun tidak aktif.');
        abort_unless(in_array($user->role, $roles, true), 403, 'Akses ditolak.');

        return $next($request);
    }
}
