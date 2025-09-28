<?php

use Modules\Sales\Http\Controllers\IncomingOrderController;
use Modules\Sales\Http\Controllers\OutgoingShipmentController;
use Modules\Sales\Http\Controllers\ServiceController;
use Modules\Sales\Http\Controllers\OutgoingOfferController;
use Illuminate\Support\Facades\Route;

// Commented out unused controllers (not implemented yet)
// use Modules\Sales\Http\Controllers\InvoiceController;
use Modules\Sales\Http\Controllers\ReturnInvoiceController;
// use Modules\Sales\Http\Controllers\SalesHelperController;

/*
|--------------------------------------------------------------------------
| Sales Module API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with 'api/v1/sales-management'
| All routes require authentication via Sanctum
*/

Route::middleware(['auth:sanctum'])->prefix('v1/sales-management')->group(function () {

    // ========================================
    // OUTGOING OFFERS MANAGEMENT
    // ========================================
    Route::prefix('outgoing-offers')->name('sales-management.outgoing-offers.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [OutgoingOfferController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [OutgoingOfferController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [OutgoingOfferController::class, 'show'])
            ->name('show-details');
        Route::put('/update-offer/{id}', [OutgoingOfferController::class, 'update'])
            ->name('update-offer');
        Route::delete('/delete-offer/{id}', [OutgoingOfferController::class, 'destroy'])
            ->name('delete-offer');

        // Status management operations
        Route::patch('/status-approve/{id}', [OutgoingOfferController::class, 'approve'])
            ->name('status-approve');
        Route::patch('/status-send/{id}', [OutgoingOfferController::class, 'send'])
            ->name('status-send');
        Route::patch('/status-cancel/{id}', [OutgoingOfferController::class, 'cancel'])
            ->name('status-cancel');
    });

    // ========================================
    // INCOMING ORDERS MANAGEMENT
    // ========================================
    Route::prefix('incoming-orders')->name('sales-management.incoming-orders.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [IncomingOrderController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [IncomingOrderController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [IncomingOrderController::class, 'show'])
            ->name('show-details');
        Route::put('/update-order/{id}', [IncomingOrderController::class, 'update'])
            ->name('update-order');
        Route::delete('/delete-order/{id}', [IncomingOrderController::class, 'destroy'])
            ->name('delete-order');

        // Soft delete operations
        Route::post('/restore-order/{id}', [IncomingOrderController::class, 'restore'])
            ->name('restore-order');

        // Form data and helper endpoints
        Route::get('/form-data/get-complete-data', [IncomingOrderController::class, 'getFormData'])
            ->name('get-form-data');
        Route::get('/form-data/get-search-options', [IncomingOrderController::class, 'getSearchFormData'])
            ->name('get-search-form-data');

        // Search and lookup endpoints
        Route::get('/search/find-customers', [IncomingOrderController::class, 'searchCustomers'])
            ->name('find-customers');
        Route::get('/search/find-items', [IncomingOrderController::class, 'searchItems'])
            ->name('find-items');

        // External data endpoints
        Route::get('/external/get-live-exchange-rate', [IncomingOrderController::class, 'getLiveExchangeRate'])
            ->name('get-live-exchange-rate');
    });

    // ========================================
    // OUTGOING SHIPMENTS MANAGEMENT
    // ========================================
    Route::prefix('outgoing-shipments')->name('sales-management.outgoing-shipments.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [OutgoingShipmentController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [OutgoingShipmentController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [OutgoingShipmentController::class, 'show'])
            ->name('show-details');
        Route::put('/update-shipment/{id}', [OutgoingShipmentController::class, 'update'])
            ->name('update-shipment');
        Route::delete('/delete-shipment/{id}', [OutgoingShipmentController::class, 'destroy'])
            ->name('delete-shipment');

        // Restore deleted shipment
        Route::post('/restore-shipment/{id}', [OutgoingShipmentController::class, 'restore'])
            ->name('restore-shipment');

        // Preview/Display complete shipment data
        Route::get('/preview-shipment/{id}', [OutgoingShipmentController::class, 'preview'])
            ->name('preview-shipment');

        // Form data and helper endpoints
        Route::get('/form-data/get-complete-data', [OutgoingShipmentController::class, 'getFormData'])
            ->name('get-form-data');

        // Search and lookup endpoints
        Route::get('/search/find-customers', [OutgoingShipmentController::class, 'searchCustomers'])
            ->name('find-customers');
        Route::get('/search/find-items', [OutgoingShipmentController::class, 'searchItems'])
            ->name('find-items');

    // Services Routes
    Route::prefix('services')->name('services.')->group(function () {
        // Basic CRUD operations
        Route::get('/list-all', [ServiceController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [ServiceController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [ServiceController::class, 'show'])
            ->name('show-details');
        Route::put('/update-service/{id}', [ServiceController::class, 'update'])
            ->name('update-service');
        Route::delete('/delete-service/{id}', [ServiceController::class, 'destroy'])
            ->name('delete-service');

        // Form data and helper endpoints
        Route::get('/form-data/get-complete-data', [ServiceController::class, 'getFormData'])
            ->name('get-form-data');

        // Search and lookup endpoints
        Route::get('/search/find-customers', [ServiceController::class, 'searchCustomers'])
            ->name('find-customers');
        Route::get('/search/find-accounts', [ServiceController::class, 'searchAccounts'])
            ->name('find-accounts');

        // Account integration endpoints
        Route::get('/accounts/get-all-numbers', [ServiceController::class, 'getAllAccountNumbers'])
            ->name('get-all-account-numbers');
        Route::get('/accounts/get-by-number', [ServiceController::class, 'getAccountByNumber'])
            ->name('get-account-by-number');
        Route::get('/accounts/get-by-name', [ServiceController::class, 'getAccountByName'])
            ->name('get-account-by-name');
    });
    });

    // ========================================
    // INVOICES MANAGEMENT (COMMENTED OUT - CONTROLLER NOT IMPLEMENTED YET)
    // ========================================
    /*
    Route::prefix('invoices')->name('sales-management.invoices.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [InvoiceController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [InvoiceController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [InvoiceController::class, 'show'])
            ->name('show-details');
        Route::put('/update-invoice/{id}', [InvoiceController::class, 'update'])
            ->name('update-invoice');
        Route::delete('/delete-invoice/{id}', [InvoiceController::class, 'destroy'])
            ->name('delete-invoice');

        // Advanced search and filtering
        Route::get('/search', [InvoiceController::class, 'search'])
            ->name('search');
        Route::get('/search-form-data', [InvoiceController::class, 'getSearchFormData'])
            ->name('search-form-data');
        Route::get('/sortable-fields', [InvoiceController::class, 'getSortableFields'])
            ->name('sortable-fields');

        // Soft delete management
        Route::get('/deleted', [InvoiceController::class, 'getDeleted'])
            ->name('deleted');
        Route::post('/restore-invoice/{id}', [InvoiceController::class, 'restore'])
            ->name('restore-invoice');
        Route::delete('/force-delete/{id}', [InvoiceController::class, 'forceDelete'])
            ->name('force-delete');
    });
    */

    // ========================================
    // RETURN INVOICES MANAGEMENT
    // ========================================
    Route::prefix('return-invoices')->name('sales-management.return-invoices.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [ReturnInvoiceController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [ReturnInvoiceController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [ReturnInvoiceController::class, 'show'])
            ->name('show-details');
        Route::put('/update-return-invoice/{id}', [ReturnInvoiceController::class, 'update'])
            ->name('update-return-invoice');
        Route::delete('/delete-return-invoice/{id}', [ReturnInvoiceController::class, 'destroy'])
            ->name('delete-return-invoice');

        // Advanced search and filtering
        Route::get('/search', [ReturnInvoiceController::class, 'search'])
            ->name('search');
        Route::get('/search-form-data', [ReturnInvoiceController::class, 'getSearchFormData'])
            ->name('search-form-data');
        Route::get('/sortable-fields', [ReturnInvoiceController::class, 'getSortableFields'])
            ->name('sortable-fields');

        // Soft delete management
        Route::get('/deleted', [ReturnInvoiceController::class, 'getDeleted'])
            ->name('deleted');
        Route::post('/restore-return-invoice/{id}', [ReturnInvoiceController::class, 'restore'])
            ->name('restore-return-invoice');
        Route::delete('/force-delete/{id}', [ReturnInvoiceController::class, 'forceDelete'])
            ->name('force-delete');

        // Helper endpoints for return invoice management
        Route::get('/search-customers', [ReturnInvoiceController::class, 'searchCustomers'])
            ->name('search-customers');
        Route::get('/customer-by-number', [ReturnInvoiceController::class, 'getCustomerByNumber'])
            ->name('customer-by-number');
        Route::get('/customer-by-name', [ReturnInvoiceController::class, 'getCustomerByName'])
            ->name('customer-by-name');
        Route::get('/search-items', [ReturnInvoiceController::class, 'searchItems'])
            ->name('search-items');
        Route::get('/item-by-number', [ReturnInvoiceController::class, 'getItemByNumber'])
            ->name('item-by-number');
        Route::get('/item-by-name', [ReturnInvoiceController::class, 'getItemByName'])
            ->name('item-by-name');
        Route::get('/live-exchange-rate', [ReturnInvoiceController::class, 'getLiveExchangeRate'])
            ->name('live-exchange-rate');
        Route::get('/form-data', [ReturnInvoiceController::class, 'getFormData'])
            ->name('form-data');
    });

    // ========================================
    // SERVICES MANAGEMENT
    // ========================================
    Route::prefix('services')->name('sales-management.services.')->group(function () {

        // Basic CRUD operations
        Route::get('/list-all', [ServiceController::class, 'index'])
            ->name('list-all');
        Route::post('/create-new', [ServiceController::class, 'store'])
            ->name('create-new');
        Route::get('/show-details/{id}', [ServiceController::class, 'show'])
            ->name('show-details');
        Route::put('/update-service/{id}', [ServiceController::class, 'update'])
            ->name('update-service');
        Route::delete('/delete-service/{id}', [ServiceController::class, 'destroy'])
            ->name('delete-service');

        // Advanced search and filtering
        Route::get('/search', [ServiceController::class, 'search'])
            ->name('search');
        Route::get('/search-form-data', [ServiceController::class, 'getSearchFormData'])
            ->name('search-form-data');
        Route::get('/sortable-fields', [ServiceController::class, 'getSortableFields'])
            ->name('sortable-fields');

        // Soft delete management
        Route::get('/deleted', [ServiceController::class, 'getDeleted'])
            ->name('deleted');
        Route::post('/restore-service/{id}', [ServiceController::class, 'restore'])
            ->name('restore-service');
        Route::delete('/force-delete/{id}', [ServiceController::class, 'forceDelete'])
            ->name('force-delete');

        // Helper endpoints for service management
        Route::get('/search-customers', [ServiceController::class, 'searchCustomers'])
            ->name('search-customers');
        Route::get('/search-accounts', [ServiceController::class, 'searchAccounts'])
            ->name('search-accounts');
        Route::get('/account-numbers', [ServiceController::class, 'getAllAccountNumbers'])
            ->name('account-numbers');
        Route::get('/account-by-number', [ServiceController::class, 'getAccountByNumber'])
            ->name('account-by-number');
        Route::get('/account-by-name', [ServiceController::class, 'getAccountByName'])
            ->name('account-by-name');
        Route::get('/form-data', [ServiceController::class, 'getFormData'])
            ->name('form-data');
    });

    // ========================================
    // HELPER ENDPOINTS (COMMENTED OUT - CONTROLLER NOT IMPLEMENTED YET)
    // ========================================
    /*
    // Helper endpoints for dropdowns and data fetching
    Route::prefix('helpers')->group(function () {
        Route::get('/customers', [SalesHelperController::class, 'getCustomers']);
        Route::get('/currencies', [SalesHelperController::class, 'getCurrencies']);
        Route::get('/items', [SalesHelperController::class, 'getItems']);
        Route::get('/units', [SalesHelperController::class, 'getUnits']);
        Route::get('/tax-rates', [SalesHelperController::class, 'getTaxRates']);
        Route::get('/company-vat-rate', [SalesHelperController::class, 'getCompanyVatRate']);
        Route::get('/currency-rate/{currencyId}', [SalesHelperController::class, 'getCurrencyRate']);
        Route::get('/item-details/{itemId}', [SalesHelperController::class, 'getItemDetails']);

        // New invoice-specific helper endpoints
        Route::get('/search-customers-invoice', [SalesHelperController::class, 'searchCustomersForInvoice']);
        Route::get('/search-items-invoice', [SalesHelperController::class, 'searchItemsForInvoice']);
        Route::get('/licensed-operators', [SalesHelperController::class, 'getLicensedOperators']);
        Route::get('/customer-details/{customerId}', [SalesHelperController::class, 'getCustomerDetails']);
        Route::get('/item-details-invoice/{itemId}', [SalesHelperController::class, 'getItemDetailsForInvoice']);
        Route::get('/live-currency-rate/{currencyId}', [SalesHelperController::class, 'getLiveCurrencyRateWithTax']);
    });
    */

});
