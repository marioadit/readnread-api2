<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, $roles = null)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Access Denied. You are not authorized.'], 403);
        }

        // support variadic, pipe or comma separated params
        if (is_array($roles)) {
            $allowed = $roles;
        } else {
            $allowed = preg_split('/[|,]/', (string) $roles, -1, PREG_SPLIT_NO_EMPTY);
        }

        $allowed = array_map('strtolower', $allowed);
        if (!in_array(strtolower($user->role), $allowed, true)) {
            return response()->json(['status' => false, 'message' => 'Access Denied. You are not authorized.'], 403);
        }

        return $next($request);
    }

    // public function handle(Request $request, Closure $next, $role): Response
    // {
    //     $user = Auth::user();

    //     if (!$user || $user->role !== $role) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Access Denied. You are not authorized.'
    //         ], 403);
    //     }

    //     return $next($request);
    // }
}
