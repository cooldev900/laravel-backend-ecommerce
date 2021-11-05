<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class VerifyJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'user_not_found'], 401); //means auth error in the api
            }
        } catch (TokenExpiredException $e) {
            // If the token is expired, then it will be refreshed and added to the headers
            try
            {
                $refreshed = JWTAuth::refresh(JWTAuth::getToken());
                $user = JWTAuth::setToken($refreshed)->toUser();
                header('Authorization:' . $refreshed);
            } catch (JWTException $e) {
                return response()->json(['error' => 'token_not_refreshable'], 401); //means not refreshable
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'user_not_found'], 401); //means auth error in the api
        }

        // Login the user instance for global usage
        Auth::login($user, false);

        return $next($request);
    }
}