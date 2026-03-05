<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // ログインしていない、またはオーナーでない場合はスルー
        if (!$user || !$user->isOwner()) {
            return $next($request);
        }

        // オンボーディングが完了しているかチェック
        // 1. パスワード変更が必要か
        // 2. 規約に同意しているか
        $needsOnboarding = $user->needs_password_change || is_null($user->terms_accepted_at);

        if ($needsOnboarding) {
            // オンボーディング関連のルート、ログアウト、デバッグ用ツールは除外
            $excludedRoutes = [
                'owner.onboarding.index',
                'owner.onboarding.update',
                'logout',
            ];

            if (!$request->routeIs($excludedRoutes) && !$request->is('api/*')) {
                return redirect()->route('owner.onboarding.index');
            }
        }

        return $next($request);
    }
}
