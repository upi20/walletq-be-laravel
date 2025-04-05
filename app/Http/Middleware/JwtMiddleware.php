<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if ($user == false) {
                return response()->json(['error' => 'Token not valid'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token not valid'], 401);
        }

        return $next($request);
    }
}