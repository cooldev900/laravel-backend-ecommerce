<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group([
    'prefix' => 'auth',
], function ($router) {
    // Routes for auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Routes for register
    // Route::post('register', 'Api\RegisterController@register');

});

Route::group(['middleware' => 'jwt_auth'], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');

    Route::group(['prefix' => '{store_view}/{scope}', 'middleware' => 'permission'], function () {
        Route::get('/', [DataController::class, 'getData'])->name('data.get');
    });
});
