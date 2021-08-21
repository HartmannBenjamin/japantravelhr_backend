<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is invalid',
                ], 401);
            }else if ($e instanceof TokenExpiredException) {
                return response()->json([
                    'success' => 'token_expired',
                    'message' => 'Token is expired',
                    'refreshToken' => JWTAuth::parseToken()->refresh(),
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Authorization token not found',
                ], 401);
            }
        }

        return $next($request);
    }
}
