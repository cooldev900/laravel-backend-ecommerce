<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUsers(Request $request)
    {
        $users = User::find(1)->permissions();

        $result = [];

        foreach ($users as $user) {
            array_push($result, )
        }

        return response()->json($users);
    }
}