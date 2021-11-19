<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerController;
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
    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/nationalcodes', [Controller::class, 'allNationalCodes'])->name('nationalcodes');

}); 

Route::prefix('{store_view}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        /********** Products **********/
        Route::get('/products', [ProductController::class, 'allProducts'])->name('products.all');
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
        Route::get('/customers/search', [CustomerController::class, 'allCustomers'])->name('customer.all');
        Route::get('/customers/{customerId}', [CustomerController::class, 'getCustomer'])->name('customer.index');
        Route::delete('/customers/{customerId}', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');
    });

});