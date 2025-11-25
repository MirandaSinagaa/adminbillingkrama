<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // Jika pengguna tidak terautentikasi
        if (!$request->user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Jika role pengguna tidak sesuai
        if (!$request->user()->hasRole($role))
        {
            return response()->json([
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
