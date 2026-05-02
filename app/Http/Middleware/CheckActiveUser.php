<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'غير مصرح / Unauthorized'
            ], 401);
        }

        if (!$request->user()->is_active) {
            return response()->json([
                'message' => 'حسابك غير نشط. يرجى التواصل مع الإدارة / Your account is inactive. Please contact administration.'
            ], 403);
        }

        return $next($request);
    }
}
