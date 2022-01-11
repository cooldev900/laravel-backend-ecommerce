<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreviewController;
use App\Http\Controllers\UserController;
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

Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['jwt_auth'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/nationalcodes', [Controller::class, 'allNationalCodes'])->name('nationalcodes');

});

Route::prefix('{store_view}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        /********** Products **********/
        Route::get('/products', [ProductController::class, 'allProducts'])->name('products.all');
        Route::post('/products', [ProductController::class, 'createProduct'])->name('products.create');
        Route::get('/products/{sku}', [ProductController::class, 'getProduct'])->name('products.index');
        Route::delete('/products/{sku}', [ProductController::class, 'deleteProduct'])->name('products.delete');
        Route::put('/products/{sku}', [ProductController::class, 'updateProduct'])->name('products.update');
        Route::post('/products/{sku}/media', [ProductController::class, 'updateMedia'])->name('products.media.update');

        /********** Orders **********/
        Route::get('/orders', [OrderController::class, 'allOrders'])->name('orders.all');
        Route::get('/orders/items', [OrderController::class, 'getOrderItems'])->name('orders.items.all');
        Route::get('/orders/{id}', [OrderController::class, 'getOrder'])->name('orders.index');
        Route::get('/orders/items/{id}', [OrderController::class, 'getOrderItem'])->name('orders.items.index');

        /********** Invoices **********/
        Route::get('/invoices', [InvoiceController::class, 'allInvoices'])->name('invoices.all');
        Route::get('/invoices/{id}', [InvoiceController::class, 'getInvoice'])->name('invoices.index');
        Route::post('/invoices', [InvoiceController::class, 'createInvoice'])->name('invoices.create');

        /********** Customers **********/
        Route::get('/customers', [CustomerController::class, 'allCustomers'])->name('customer.all');
        Route::get('/customers/{customerId}', [CustomerController::class, 'getCustomer'])->name('customer.index');
        Route::delete('/customers/{customerId}', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');
    });
});

Route::middleware(['jwt_auth', 'is_admin'])->group(function () {
    Route::prefix('storeviews')->group(function () {
        Route::get('/', [StoreviewController::class, 'allStoreviews'])->name('storeviews.all');
        Route::get('/{id}', [StoreviewController::class, 'getStoreview'])->name('storeviews.index');
        Route::post('/', [StoreviewController::class, 'createStoreview'])->name('storeviews.create');
        Route::put('/{id}', [StoreviewController::class, 'updateStoreview'])->name('storeviews.update');
        Route::delete('/{id}', [StoreviewController::class, 'deleteStoreview'])->name('storeviews.delete');
    });

    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'allCompanies'])->name('companies.all');
        Route::get('/{id}', [CompanyController::class, 'getCompany'])->name('companies.index');
        Route::post('/', [CompanyController::class, 'createCompany'])->name('companies.create');
        Route::put('/{id}', [CompanyController::class, 'updateCompany'])->name('companies.update');
        Route::delete('/{id}', [CompanyController::class, 'deleteCompany'])->name('companies.delete');
    });

    Route::prefix('/locations/{companyId}')->group(function () {
        Route::get('/', [LocationController::class, 'allLocations'])->name('locations.all');
        Route::get('/{id}', [LocationController::class, 'getLocation'])->name('locations.index');
        Route::post('/', [LocationController::class, 'createLocation'])->name('locations.create');
        Route::put('/{id}', [LocationController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/{id}', [LocationController::class, 'deleteLocation'])->name('locations.delete');
    });

    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::prefix('/users/{companyId}')->group(function () {
        Route::get('/', [UserController::class, 'allUsers'])->name('users.all');
        Route::get('/{id}', [UserController::class, 'getUser'])->name('users.index');
        // Route::put('/{id}', [UserController::class, 'updateLocation'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'deleteUser'])->name('users.delete');
    });

    Route::get('/roles', [StoreviewController::class, 'allRoles'])->name('roles.all');
    Route::get('/scopes', [StoreviewController::class, 'allScopes'])->name('scopes.all');
});