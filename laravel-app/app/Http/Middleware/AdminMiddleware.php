<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーがログインしていない場合
        if (!auth()->check()) {
            abort(401, '認証が必要です');
        }

        // ユーザーが管理者でない場合
        if (!auth()->user()->isAdmin()) {
            abort(403, '管理者権限が必要です');
        }

        return $next($request);
    }
}