<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CheckPermission
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
        $permissions = AuthController::getPermission(JWTAuth::user());
        $params = $request->route()->parameters();
        $action = $request->route()->methods[0];

        // Check store_view
        $has_store_view_access = in_array($params['store_view'], array_column($permissions->store_views, 'code'));
        if (!$has_store_view_access) {
            return response()->json(['error' => 'store_view_permission_denied'], 401);
        }

        // Check scope
        $has_scope_access = in_array($params['scope'], array_column($permissions->scopes, 'name'));
        if (!$has_scope_access) {
            return response()->json(['error' => 'scope_permission_denied'], 401);
        }

        // Check role
        if ($action == 'GET') {
            $has_read_role = in_array('read', array_column($permissions->roles, 'name'));
            if (!$has_read_role) {
                return response()->json(['error' => 'no_read_role'], 405); // 405: Method Not Allowed
            }
        } else if ($action == 'DELETE') {
            $has_delete_role = in_array('delete', array_column($permissions->roles, 'name'));
            if (!$has_delete_role) {
                return response()->json(['error' => 'no_delete_role'], 405); // 405: Method Not Allowed
            }
        } else {
            $has_write_role = in_array('write', array_column($permissions->roles, 'name'));
            if (!$has_write_role) {
                return response()->json(['error' => 'no_write_role'], 405); // 405: Method Not Allowed
            }

        }

        return $next($request);
    }
}