<?php

namespace App\Http\Controllers;

use App\Models\User;
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
            $location = User::find($params['id']);
            $location->delete();

            $userPermissions = UserPermission::where('user_id', $params['id']);
            $userPermissions->delete();

            return response()->json([
                'status' => 'success',
                'data' => $location,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}