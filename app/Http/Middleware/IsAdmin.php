<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = JWTAuth::user();
        if (!$user->is_admin) {
            return response()->json([
                'status' => 'error',
                'error' => 'not_admin',
                'message' => 'Only admin user can register a new user.',
            ], 402);
        }

        return $next($request);

    }
}