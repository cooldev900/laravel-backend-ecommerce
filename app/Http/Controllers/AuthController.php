<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserPermission;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('jwt_auth', ['except' => ['login', 'refresh', 'logout']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password', 'company_id');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'invalid_credentials',
                    'message' => 'The user credentials were incorrect. ',
                ], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_token',
                'message' => 'Enable to process request.',
            ], 422);
        }

        $user = JWTAuth::user();
        // $magento = $user->magento;

        return response()->json([
            'status' => 'success',
            'user' => $this->getPermission($user),
            'token' => $token,
        ], 200);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function me()
    {
        return response()->json($this->getPermission(auth()->user()));
    }

    /**
     * Log the user out ( Invalidate the token )
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        // $token = JWTAuth::parseToken();
        // dd($token);

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function refresh()
    {
        return $this->respondWithToken(JWTAuth::refresh(JWTAuth::getToken()));
    }

    /**
     * Get the user permissions.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    /**
     * Get the user permission based on JWT token.
     *
     * @param  \Model\User $user
     *
     * @return Object $user with permissions
     */

    private function getPermission($user)
    {
        $_user = $user;
        $permissions = UserPermission::where('user_id', $_user->id)->get();
        $permissions_scopes_unique = json_decode(json_encode($permissions->unique('scopes')), true);
        $permissions_store_views_unique = json_decode(json_encode($permissions->unique('store_views')), true);
        $permissions_roles_unique = json_decode(json_encode($permissions->unique('roles')), true);

        $_user['scope'] = array_column($permissions_scopes_unique, 'scopes');
        $_user['store_view'] = array_column($permissions_store_views_unique, 'store_views');
        $_user['role'] = array_column($permissions_roles_unique, 'roles');

        return $_user;
    }
}