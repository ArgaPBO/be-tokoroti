<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // $user = $request->user();

        // if (! $user) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // if ($role === 'admin' && ! $user->isAdmin()) {
        //     return $next($request);
        // }

        // if ($role === 'branch' && ! $user->isBranch()) {
        //     return $next($request);
        // }
        // return response()->json(['message' => 'Forbidden - Insufficient permissions'], 403);
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($role === 'admin' && ! $user->isAdmin()) {
            return response()->json(['message' => 'Forbidden - Admins only'], 403);
        }

        if ($role === 'employee' && ! $user->isEmployee()) {
            return response()->json(['message' => 'Forbidden - Employees only'], 403);
        }

        return $next($request);
    }
}
