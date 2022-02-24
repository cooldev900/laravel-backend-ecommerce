<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StoreviewController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TransactionController;
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
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    // Send reset password mail
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink']);
    // handle reset password form process
    Route::post('reset-password/{token}', [AuthController::class, 'callResetPassword']);
});

Route::middleware(['jwt_auth'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/nationalcodes', [Controller::class, 'allNationalCodes'])->name('nationalcodes');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

    /********** Courier **********/
    Route::post('/courier/{location_id}/label', [CourierController::class, 'createSmartLabel'])->name('courier.label.create');
});

Route::prefix('{store_view}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        /********** Products-attributes **********/
        Route::get('/products/attributes', [ProductController::class, 'getAttributes'])->name('products.attributes.all');
        Route::post('/products/attributes', [ProductController::class, 'createAttributes'])->name('products.attributes.create');
        Route::get('/products/attributes/{attributeCode}/options', [ProductController::class, 'getAttributeOptions'])->name('products.attributes.options.all');
        Route::post('/products/attributes/{attributeCode}/options', [ProductController::class, 'createAttributeOptions'])->name('products.attributes.options.create');

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
        // Route::post('/orders/{id}', [OrderController::class, 'getOrder'])->name('orders.index');
        Route::post('/orders/{orderId}/ship', [ShipmentController::class, 'createShipment'])->name('orders.shipment.create');
        Route::post('/orders/{orderId}/invoice', [InvoiceController::class, 'createInvoice'])->name('orders.invoice.create');
        Route::get('/orders/items/{id}', [OrderController::class, 'getOrderItem'])->name('orders.items.index');
        Route::post('/orders/notify-orders-are-ready-for-pickup', [OrderController::class, 'getNotify'])->name('orders.notify');

        /********** Invoices **********/
        Route::get('/invoices', [InvoiceController::class, 'allInvoices'])->name('invoices.all');
        Route::get('/invoices/{id}', [InvoiceController::class, 'getInvoice'])->name('invoices.index');
        Route::post('/invoices/{id}/refund', [InvoiceController::class, 'refundInvoice'])->name('invoices.refund');

        /********** Customers **********/
        Route::get('/customers', [CustomerController::class, 'allCustomers'])->name('customer.all');
        Route::get('/customers/{customerId}', [CustomerController::class, 'getCustomer'])->name('customer.index');
        Route::delete('/customers/{customerId}', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');

        /********** Shipments **********/
        Route::get('/shipments', [ShipmentController::class, 'allShipments'])->name('shipments.all');
        Route::get('/shipments/{shipmentId}', [ShipmentController::class, 'getShipment'])->name('shipments.index');
        Route::post('/shipments', [ShipmentController::class, 'createShipment'])->name('shipments.create');
        Route::post('/shipments/track', [ShipmentController::class, 'createShipmentTrack'])->name('shipments.track.create');
        Route::delete('/shipments/track/{trackId}', [ShipmentController::class, 'deleteShipmentTrack'])->name('shipments.track.delete');
        // Route::delete('/shipments/{shipmentId}', [CustomerController::class, 'deleteShipment'])->name('shipments.delete');

        /********** Transactions **********/
        Route::get('/transactions', [TransactionController::class, 'allTransactions'])->name('transactions.all');
        Route::get('/transactions/{id}', [TransactionController::class, 'getTransaction'])->name('transactions.index');
        // Route::post('/shipments', [ShipmentController::class, 'createShipment'])->name('shipments.create');
        // Route::post('/shipments/track', [ShipmentController::class, 'createShipmentTrack'])->name('shipments.track.create');
        // Route::delete('/shipments/track/{trackId}', [ShipmentController::class, 'deleteShipmentTrack'])->name('shipments.track.delete');

        /********** Stripe **********/
        Route::post('/stripe/transaction', [StripeController::class, 'capturePaymentIntent'])->name('stripe.transaction.index');
        Route::post('/stripe/refund', [StripeController::class, 'createRefund'])->name('stripe.refund.create');

        /********** Paypal **********/
        Route::post('/paypal/transaction', [PaypalController::class, 'capturePaymentIntent'])->name('paypal.transaction.index');
        Route::post('/paypal/refund', [PaypalController::class, 'createRefund'])->name('payapl.refund.create');
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
        // Route::get('/', [LocationController::class, 'allLocations'])->name('locations.all');
        Route::get('/{id}', [LocationController::class, 'getLocation'])->name('locations.index');
        Route::post('/', [LocationController::class, 'createLocation'])->name('locations.create');
        Route::put('/{id}', [LocationController::class, 'updateLocation'])->name('locations.update');
        Route::delete('/{id}', [LocationController::class, 'deleteLocation'])->name('locations.delete');
    });

    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');
    Route::prefix('/users/{companyId}')->group(function () {
        Route::get('/', [UserController::class, 'allUsers'])->name('users.all');
        Route::get('/{id}', [UserController::class, 'getUser'])->name('users.index');
        Route::put('/{id}', [UserController::class, 'updateUser'])->name('users.update');
        Route::delete('/{id}', [UserController::class, 'deleteUser'])->name('users.delete');
    });

    Route::get('/roles', [StoreviewController::class, 'allRoles'])->name('roles.all');
    Route::get('/scopes', [StoreviewController::class, 'allScopes'])->name('scopes.all');
});

/***** Public apis *****/

Route::get('/locations/{companyId}', [LocationController::class, 'allLocations'])->name('locations.all');

Route::prefix('/enquiries')->group(function () {
    Route::post('/getAll', [EnquiryController::class, 'allEnquiries'])->name('enquiries.all');
    Route::get('/{client_id}/{store_id}', [EnquiryController::class, 'getEnquiries'])->name('enquiries.index');
    Route::post('/', [EnquiryController::class, 'createEnquiry'])->name('enquiries.create');
    Route::put('/{id}', [EnquiryController::class, 'updateEnquiry'])->name('enquiries.update');
    Route::delete('/{id}', [EnquiryController::class, 'deleteEnquiry'])->name('enquiries.delete');
});
