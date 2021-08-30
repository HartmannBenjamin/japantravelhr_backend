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
     * JWT Middleware.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(
                    [
                    'success' => false,
                    'message' => __('auth.invalid_token'),
                    ],
                    401
                );
            } elseif ($e instanceof TokenExpiredException) {
                return response()->json(
                    [
                    'success' => 'token_expired',
                    'message' => __('auth.expired_token'),
                    ],
                    401
                );
            } else {
                return response()->json(
                    [
                    'success' => false,
                    'message' => __('auth.token_not_found'),
                    ],
                    401
                );
            }
        }

        return $next($request);
    }
}
