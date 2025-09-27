<?php

use Modules\Sales\Http\Controllers\IncomingOrderController;
use Modules\Sales\Http\Controllers\OutgoingShipmentController;
use Modules\Sales\Http\Controllers\ReturnInvoiceController;
use Modules\Sales\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\InvoiceController;
use Modules\Sales\Http\Controllers\OutgoingOfferController;
use Modules\Sales\Http\Controllers\SalesHelperController;

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
    });

    // ========================================
    // INVOICES MANAGEMENT
    // ========================================
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
    });

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
    });

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

});
