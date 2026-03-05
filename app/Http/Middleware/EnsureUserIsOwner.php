<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->isAdmin() && !session()->has('impersonating_admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
