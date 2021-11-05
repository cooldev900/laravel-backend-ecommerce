<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
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

Route::prefix('auth')->group(function () {
    // Routes for auth
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // Routes for register
    // Route::post('register', 'Api\RegisterController@register');

});

Route::middleware(['jwt_auth'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('me', [AuthController::class, 'me'])->name('me');

});

Route::prefix('{store_view}/{scope}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        /********** Products **********/
        Route::get('/', [ProductController::class, 'all'])->name('products.all');
        Route::delete('/{sku}', [ProductController::class, 'delete'])->name('product.delete');
    });
});