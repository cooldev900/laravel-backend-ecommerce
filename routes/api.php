<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CourierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeGroupController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\StoreviewController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\CybersourceController;
use App\Http\Controllers\CheckoutComController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarclayCardController;
use App\Http\Controllers\ElasticSearchController;
use App\Http\Controllers\ReportController;

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

Route::middleware(['jwt_auth'])->group(function () {
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/nationalcodes', [Controller::class, 'allNationalCodes'])->name('nationalcodes');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

    /********** Courier **********/
    Route::post('/courier/{location_id}/label', [CourierController::class, 'createSmartLabel'])->name('courier.label.create');

    /********** Sending Email **********/
    Route::post('/send-email', [Controller::class, 'sendEmail'])->name('email.send');
    Route::get('/websites', [ProductController::class, 'getWebsites'])->name('websites.all');

    Route::post('/reports/', [ReportController::class, 'createReportData'])->name('report.create');
    Route::get('/reports/', [ReportController::class, 'getReportingData'])->name('report.get');
});

Route::prefix('{store_view}')->group(function () {
    Route::middleware(['jwt_auth', 'permission'])->group(function () {
        Route::get('/getCategoriesList', [ReportController::class, 'getCategoriesList'])->name('reports.getCategoriesList');

        Route::get('/marketing/getAllCoupons', [MarketingController::class, 'getAllCoupons'])->name('marketing.coupons.all');
        Route::get('/marketing/getSalesRule/{rule_id}', [MarketingController::class, 'getSalesRule'])->name('marketing.coupons.getSalesRule');

        /********** Products-attributes **********/
        Route::get('/products/attributes', [ProductController::class, 'getAttributes'])->name('products.attributes.all');
        Route::post('/products/attributes', [ProductController::class, 'createAttributes'])->name('products.attributes.create');
        Route::get('/products/attributes/{attributeCode}/options', [ProductController::class, 'getAttributeOptions'])->name('products.attributes.options.all');
        Route::get('/products/attribute-sets/{attributeSetId}/attributes', [ProductController::class, 'getAttributeSets'])->name('products.attributes.set.all');
        Route::post('/products/attributes/{attributeCode}/options', [ProductController::class, 'createAttributeOptions'])->name('products.attributes.options.create');
        Route::get('/products/getProductFilters/{client_id}', [ProductController::class, 'getProductFilters'])->name('products.getProductFilters');

        /********** Products **********/
        Route::get('/products', [ProductController::class, 'allProducts'])->name('products.all');
        Route::get('/products/graphql', [ProductController::class, 'gqlProducts'])->name('products.gql.all');
        Route::get('/products/elasticsearch', [ElasticSearchController::class, 'allProducts'])->name('products.elasticsearch.all');
        Route::post('/products', [ProductController::class, 'createProduct'])->name('products.create');
        Route::get('/products/{sku}', [ProductController::class, 'getProduct'])->name('products.index');
        Route::delete('/products/{sku}', [ProductController::class, 'deleteProduct'])->name('products.delete');
        Route::put('/products/{sku}', [ProductController::class, 'updateProduct'])->name('products.update');
        Route::post('/products/{sku}/media', [ProductController::class, 'updateMedia'])->name('products.media.update');
        Route::post('/configurable-products/{sku}/child', [ProductController::class, 'assignChildProducts'])->name('products.configurableProducts.assign');
        Route::post('/configurable-products/{sku}/options', [ProductController::class, 'setConfigurableAttribute'])->name('products.configurableProducts.set');


        /********** Orders **********/
        Route::get('/orders', [OrderController::class, 'allOrders'])->name('orders.all');
        Route::post('/orders', [OrderController::class, 'createOrder'])->name('orders.create');
        Route::put('/orders', [OrderController::class, 'updateOrder'])->name('orders.update');
        Route::put('/orders/{parent_id}', [OrderController::class, 'updateShippingAddress'])->name('orders.updateShippingAddress');
        Route::get('/orders/getOrderField/{orderId}', [OrderController::class, 'getOrderField'])->name('orders.getOrderField');
        Route::get('/orders/items', [OrderController::class, 'getOrderItems'])->name('orders.items.all');
        Route::get('/orders/open-carts', [OrderController::class, 'openCarts'])->name('orders.openCarts');
        Route::get('/orders/{id}', [OrderController::class, 'getOrder'])->name('orders.index');
        // Route::post('/orders/{id}', [OrderController::class, 'getOrder'])->name('orders.index');
        Route::post('/orders/{orderId}/ship', [ShipmentController::class, 'createShipment'])->name('orders.shipment.create');
        Route::post('/orders/{orderId}/invoice', [InvoiceController::class, 'createInvoice'])->name('orders.invoice.create');
        Route::post('/orders/{orderId}/comments', [OrderController::class, 'createOrderComment'])->name('orders.comments.create');
        Route::get('/orders/items/{id}', [OrderController::class, 'getOrderItem'])->name('orders.items.index');
        Route::post('/orders/notify-orders-are-ready-for-pickup', [OrderController::class, 'getNotify'])->name('orders.notify');
        Route::post('/orders/{orderId}/refund', [OrderController::class, 'refundOrder'])->name('orders.refund');

        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('orders.items.calcel');
        Route::get('/orders/{id}/status', [OrderController::class, 'getOrderItemStatus'])->name('orders.items.itemStatus');

        /********** Enquiries **********/
        Route::prefix('/enquiries')->group(function () {
            Route::get('/', [EnquiryController::class, 'allEnquiries'])->name('enquiries.all');
            Route::get('/{client_id}/{store_id}', [EnquiryController::class, 'getEnquiries'])->name('enquiries.index');
            Route::put('/{id}', [EnquiryController::class, 'updateEnquiry'])->name('enquiries.update');
            Route::delete('/{id}', [EnquiryController::class, 'deleteEnquiry'])->name('enquiries.delete');
        });

        /********** Invoices **********/
        Route::get('/invoices', [InvoiceController::class, 'allInvoices'])->name('invoices.all');
        Route::get('/invoices/{id}', [InvoiceController::class, 'getInvoice'])->name('invoices.index');
        //        Route::post('/invoices/{id}/refund', [InvoiceController::class, 'refundInvoice'])->name('invoices.refund');

        /********** Customers **********/
        Route::get('/customers', [CustomerController::class, 'allCustomers'])->name('customer.all');
        Route::get('/customers/{customerId}', [CustomerController::class, 'getCustomer'])->name('customer.index');
        Route::put('/customers/{customerId}', [CustomerController::class, 'updateCustomer'])->name('customer.update');
        Route::get('/customers/{customerId}/billingAddress', [CustomerController::class, 'getCustomerBillingAddress'])->name('customer.billingAddress');
        Route::get('/customers/{customerId}/shippingAddress', [CustomerController::class, 'getCustomerShippingAddress'])->name('customer.shipping');
        Route::delete('/customers/{customerId}', [CustomerController::class, 'deleteCustomer'])->name('customer.delete');

        /********** Shipments **********/
        Route::get('/shipments', [ShipmentController::class, 'allShipments'])->name('shipments.all');
        Route::get('/shipments/{shipmentId}', [ShipmentController::class, 'getShipment'])->name('shipments.index');
        Route::post('/shipments', [ShipmentController::class, 'createShipment'])->name('shipments.create');
        Route::post('/shipments/track', [ShipmentController::class, 'createShipmentTrack'])->name('shipments.track.create');
        Route::delete('/shipments/track/{trackId}', [ShipmentController::class, 'deleteShipmentTrack'])->name('shipments.track.delete');
        // Route::delete('/shipments/{shipmentId}', [CustomerController::class, 'deleteShipment'])->name('shipments.delete');

        /********** Refunds **********/
        Route::get('/refunds', [RefundController::class, 'allRefunds'])->name('refunds.all');

        /********** Stripe **********/
        Route::post('/stripe/transaction', [StripeController::class, 'getTransaction'])->name('stripe.transaction.index');
        Route::post('/stripe/capture', [StripeController::class, 'capturePaymentIntent'])->name('stripe.refund.create');
        Route::post('/stripe/refund', [StripeController::class, 'createRefund'])->name('stripe.refund.refund');

        Route::get('/cybersource/transaction/{id}', [CybersourceController::class, 'getTransaction'])->name('cyersource.transaction.index');
        Route::post('/cybersource/capture', [CybersourceController::class, 'capture'])->name('cyersource.refund.create');
        Route::post('/cybersource/void', [CybersourceController::class, 'void'])->name('cyersource.refund.void');

        /********** CheckoutCom **********/
        Route::post('/checkoutcom/capture', [CheckoutComController::class, 'capturePayment'])->name('checkoutcom.transaction.index');
        Route::post('/checkoutcom/refund', [CheckoutComController::class, 'refundPayment'])->name('checkoutcom.refund.create');
        Route::post('/checkoutcom/void', [CheckoutComController::class, 'voidPayment'])->name('checkoutcom.voidPayment.void');
        Route::get('/checkoutcom/getPaymentDetails', [CheckoutComController::class, 'getPaymentDetails'])->name('checkoutcom.getPaymentDetails.void');

        /********** Paypal **********/
        Route::post('/paypal/transaction', [PaypalController::class, 'capturePaymentIntent'])->name('paypal.transaction.index');
        Route::post('/paypal/refund', [PaypalController::class, 'createRefund'])->name('payapl.refund.create');

        /********** BarclayCard **********/
        Route::get('/barclaycard/transaction', [BarclayCardController::class, 'getTransaction'])->name('barclaycard.transaction');
        Route::post('/barclaycard/capture', [BarclayCardController::class, 'capturePaymentIntent'])->name('barclaycard.capture');
        Route::post('/barclaycard/refund', [BarclayCardController::class, 'createRefund'])->name('barclaycard.refund');

        Route::prefix('/appointments/{companyId}')->group(function () {
            Route::get('/getAllAppointments', [AppointmentController::class, 'getAllAppointments'])->name('products.getAllAppointments');
            Route::get('/', [AppointmentController::class, 'getSlots'])->name('products.getSlots');
            Route::get('/getAppointment/{id}', [AppointmentController::class, 'getAppointment'])->name('products.getAppointment');
            Route::post('/', [AppointmentController::class, 'setSlot'])->name('products.setSlots');
            Route::delete('/', [AppointmentController::class, 'deleteSlot'])->name('products.deleteSlots');
            Route::get('/fetchSlotData', [AppointmentController::class, 'fetchSlotData'])->name('products.fetchSlotData');
            Route::get('/fetchTechnicians', [AppointmentController::class, 'fetchTechnicians'])->name('products.fetchTechnicians');
        });
    });
});

Route::middleware(['jwt_auth', 'is_admin'])->group(function () {
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'getReportData'])->name('companies.all');
    });

    Route::get('/products/getAllAttributes', [ProductController::class, 'getAttributeSets'])->name('products.attributes.get.all');

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

    Route::prefix('/attributes/{companyId}')->group(function () {
        Route::get('/', [AttributeController::class, 'allattributes'])->name('attributes.all');
        Route::get('/{id}', [AttributeController::class, 'getAttribute'])->name('attributes.index');
        Route::post('/', [AttributeController::class, 'createAttribute'])->name('attributes.create');
        Route::put('/{id}', [AttributeController::class, 'updateAttribute'])->name('attributes.update');
        Route::delete('/{id}', [AttributeController::class, 'deleteAttribute'])->name('attributes.delete');
    });

    Route::prefix('/attributeGroup/{companyId}')->group(function () {
        Route::get('/', [AttributeGroupController::class, 'allattributes'])->name('attributes.all1');
        Route::get('/{id}', [AttributeGroupController::class, 'getAttribute'])->name('attributes.index1');
        Route::post('/', [AttributeGroupController::class, 'createAttribute'])->name('attributes.create1');
        Route::put('/{id}', [AttributeGroupController::class, 'updateAttribute'])->name('attributes.update1');
        Route::delete('/{id}', [AttributeGroupController::class, 'deleteAttribute'])->name('attributes.delete1');
    });

    Route::prefix('/resource/{companyId}')->group(function () {
        Route::get('/', [ResourceController::class, 'allattributes'])->name('attributes.all2');
        Route::get('/{id}', [ResourceController::class, 'getAttribute'])->name('attributes.index2');
        Route::post('/', [ResourceController::class, 'createAttribute'])->name('attributes.create2');
        Route::put('/{id}', [ResourceController::class, 'updateAttribute'])->name('attributes.update2');
        Route::delete('/{id}', [ResourceController::class, 'deleteAttribute'])->name('attributes.delete2');
    });

    Route::post('/auth/register', [AuthController::class, 'register'])->name('register');

    Route::get('/users/logs', [AuthController::class, 'getLogs'])->name('users.logs.all');
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
Route::post('/enquiries', [EnquiryController::class, 'createEnquiry'])->name('enquiries.create');
Route::post('/image-blob', [Controller::class, 'getImageBlob'])->name('getImageBlob');
Route::post('/mail/internal/new-order', [OrderController::class, 'newOrder'])->name('webhooks.newOrder');
Route::post('/mail/external/new-order', [OrderController::class, 'newExternalOrder'])->name('webhooks.newOrder1');
Route::post('/mail/internal/new-enquiry', [OrderController::class, 'newEnquiry'])->name('webhooks.newEnquiry');

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('passcodeLogin', [AuthController::class, 'passcodeLogin'])->name('passcodeLogin');
    // Send reset password mail
    Route::post('forgot-password', [AuthController::class, 'sendPasswordResetLink']);
    // handle reset password form process
    Route::post('reset-password/{token}', [AuthController::class, 'callResetPassword']);
});

Route::prefix('vehicle')->group(function () {
    Route::post('vehicle-selector/national-code', [ElasticSearchController::class, 'getNationalCodeData']);
    Route::post('vehicle-selector/step/{key}', [ElasticSearchController::class, 'getSelectorStepData']);
});

Route::get('/appointments', [AppointmentController::class, 'getSlots'])->name('getSlots');
Route::post('/appointments', [AppointmentController::class, 'setSlot'])->name('setSlot');
Route::delete('/appointments', [AppointmentController::class, 'deleteSlot'])->name('deleteSlot');
Route::get('/appointments/all', [AppointmentController::class, 'getAllAppointments'])->name('getAllAppointments');
Route::get('/available-slot', [AppointmentController::class, 'isAvaibleSlot'])->name('isAvaibleSlot');

Route::get('/cybersource/transaction/{id}', [CybersourceController::class, 'getTransaction'])->name('cyersource.transaction.index1');
Route::post('/cybersource/capture', [CybersourceController::class, 'capture'])->name('cyersource.refund.create1');
Route::post('/cybersource/void', [CybersourceController::class, 'void'])->name('cyersource.refund.void1');

Route::prefix('providers')->group(function () {
    Route::post('/', [ProviderController::class, 'store'])->name('providers.create');
    Route::delete('/{id}', [ProviderController::class, 'destroy'])->name('providers.delete');
    Route::post('/{id}/addFields', [ProviderController::class, 'addFields'])->name('providers.addFields');
    Route::put('/{id}/editField', [ProviderController::class, 'editField'])->name('providers.editField');
    Route::delete('/{id}/deleteField', [ProviderController::class, 'deleteField'])->name('providers.deleteField');
});

Route::get('/report/test/', [ReportController::class, 'getReportData'])->name('report.all');
