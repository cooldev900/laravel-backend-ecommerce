<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
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
    Route::get('/nationalcodes', [Controller::class, 'allNationalCodes'])->name('nationalcodes');

});

Route::prefix('{store_view}/{scope}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        /********** Products **********/
        Route::get('/', [ProductController::class, 'allProducts'])->name('products.all');
        Route::get('/{sku}', [ProductController::class, 'getProduct'])->name('products.index');
        Route::delete('/{sku}', [ProductController::class, 'deleteProduct'])->name('products.delete');
        Route::put('/{sku}', [ProductController::class, 'updateProduct'])->name('products.update');
    });
});