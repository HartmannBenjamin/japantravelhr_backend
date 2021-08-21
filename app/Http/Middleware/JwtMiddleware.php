<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class JwtMiddleware extends BaseMiddleware
{

    /**
     * JWT Middleware.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws JWTException
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
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
                ]);
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
