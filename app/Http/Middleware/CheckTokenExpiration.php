<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helper\ApiResponse;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $token = $request->user()->currentAccessToken();
            
            if ($token->expires_at && now('Asia/Jakarta')->gt($token->expires_at)) {
                $token->delete();
                return ApiResponse::error('Token has expired', 401);
            }
        }

        return $next($request);
    }
} 