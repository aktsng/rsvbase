<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if (!session()->has('impersonating_admin_id')) {
                abort(403, '管理者権限が必要です。');
            }
        }

        return $next($request);
    }
}
