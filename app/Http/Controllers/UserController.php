<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLocation;
use App\Models\UserPermission;
use Exception;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function allUsers()
    {
        $users = User::all();

        $result = [];
        foreach ($users as $user) {
            array_push($result, $user);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function getUser(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $user = User::find($params['id']);

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_user',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteUser(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $userLocations = UserLocation::where('user_id', $params['id']);
            $userLocations->delete();

            $userPermissions = UserPermission::where('user_id', $params['id']);
            $userPermissions->delete();

            $user = User::find($params['id']);
            $user->delete();

            return response()->json([
                'status' => 'success',
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            // $request->validate([
            //     'email' => 'required|regex:/^.+@.+$/i',
            //     'name' => 'required|string',
            //     'password' => 'string',
            //     'company_name' => 'required|string',
            //     'scopes' => 'array',
            //     'store_views' => 'array',
            //     'roles' => 'array',
            //     'is_admin' => 'numeric',
            // ]);

            $params = $request->route()->parameters();

            //Register a new user
            $user = User::find($params['id']);
            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'company_name' => $request->input('company_name'),
                'image_base_url' => $request->input('image_base_url'),
                'is_admin' => $request->input('is_admin'),
            ]);
            if (!empty($request->input('password'))) {
                $user->update([
                    'password' => bcrypt($request->input('password')),
                ]);
            };

            //Set user permissions
            $userPermissions = UserPermission::where('user_id', $params['id']);
            $userPermissions->delete();

            foreach ($request->input('scopes') as $scope) {
                foreach ($request->input('store_views') as $store_view) {
                    foreach ($request->input('roles') as $role) {
                        $userPermission = new UserPermission();
                        $userPermission->user_id = $user->id;
                        $userPermission->scope_id = $scope;
                        $userPermission->store_view_id = $store_view;
                        $userPermission->role_id = $role;
                        $userPermission->save();
                    }
                }
            }

            //Set user locations
            $userLocations = UserLocation::where('user_id', $params['id']);
            $userLocations->delete();

            foreach ($request->input('locations') as $location) {
                $newUserLocation = new UserLocation();
                $newUserLocation->user_id = $user->id;
                $newUserLocation->location_id = $location;
                $newUserLocation->save();
            }

            return response()->json([
                'status' => 'success',
                'new_user' => $this->getPermission($user),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'invalid_user_data',
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}