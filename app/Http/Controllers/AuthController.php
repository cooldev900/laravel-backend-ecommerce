<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPermission;
use Exception;
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
        $credentials = $request->only('email', 'password', 'company_name');

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
     * Register user if current user has admin permission.
     *
     * @param
     * @return \Illuminate\Http\JsonResponse
     */

    public function register(Request $request)
    {
        try {
            $user = JWTAuth::user();
            // Check if user is admin
            if (!$user->is_admin) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'not_admin',
                    'message' => 'Only admin user can register a new user.',
                ], 402);
            }

            $request->validate([
                'email' => 'required',
                'name' => 'required',
                'password' => 'required',
                'company_name' => 'required',
                'scopes' => 'required|array|min:1',
                'store_views' => 'required|array|min:1',
                'roles' => 'required|array|min:1',
            ]);

            $newUser = new User();
            $newUser->email = $request->input('email');
            $newUser->name = $request->input('name');
            $newUser->company_name = $request->input('company_name');
            $newUser->is_admin = $request->input('is_admin');
            $newUser->password = bcrypt($request->input('password'));
            $newUser->save();

            foreach ($request->input('scopes') as $scope) {
                foreach ($request->input('store_views') as $store_view) {
                    foreach ($request->input('roles') as $role) {
                        $newUserPermission = new UserPermission();
                        $newUserPermission->user_id = $newUser->id;
                        $newUserPermission->scope_id = $scope;
                        $newUserPermission->store_view_id = $store_view;
                        $newUserPermission->role_id = $role;
                        $newUserPermission->save();
                    }
                }
            }

            return response()->json($this->getPermission($newUser));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'invalid_user_data',
                'message' => $e->getMessage(),
            ], 500);
        }
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

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get the user permission based on JWT token.
     *
     * @param  \Model\User $user
     *
     * @return Object $user with permissions
     */

    public function getPermission($user)
    {
        $_user = $user;
        $permissions = UserPermission::where('user_id', $_user->id)->get();
        $permissions_scopes_unique = json_decode(json_encode($permissions->unique('scopes')), true);
        $permissions_store_views_unique = json_decode(json_encode($permissions->unique('store_views')), true);
        $permissions_roles_unique = json_decode(json_encode($permissions->unique('roles')), true);

        $_user['scopes'] = array_column($permissions_scopes_unique, 'scopes');
        $_user['store_views'] = array_column($permissions_store_views_unique, 'store_views');
        $_user['roles'] = array_column($permissions_roles_unique, 'roles');

        return $_user;
    }
}