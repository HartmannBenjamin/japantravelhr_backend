<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

/**
 * Class JwtMiddleware
 *
 * @package App\Http\Middleware
 */
class JwtMiddleware extends BaseMiddleware
{

    /**
     * JWT Middleware
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
                $message = __('auth.invalid_token');
            } elseif ($e instanceof TokenExpiredException) {
                $message = __('auth.expired_token');
            } else {
                $message =  __('auth.token_not_found');
            }

            return response()->json(
                [
                    'success' => false,
                    'message' => $message,
                ],
                401
            );
        }

        return $next($request);
    }
}
