<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchases\Http\Controllers\ExpenseController;
use Modules\Purchases\Http\Controllers\PurchaseReferenceInvoiceController;
use Modules\Purchases\Http\Controllers\IncomingOfferController;
use Modules\Purchases\Http\Controllers\IncomingShipmentController;
use Modules\Purchases\Http\Controllers\InvoiceController;
use Modules\Purchases\Http\Controllers\OutgoingOrderController;
use Modules\Purchases\Http\Controllers\ReturnInvoiceController;

Route::middleware(['auth:sanctum'])->prefix('v1/purchase')->group(function () {

    Route::prefix('incoming-offers')->as('incoming-offers.')->group(function () {
        // CRUD operations
        Route::get('/list', [IncomingOfferController::class, 'index'])->name('index');
        Route::post('/create', [IncomingOfferController::class, 'store'])->name('store');
        Route::get('/details/{id}', [IncomingOfferController::class, 'show'])->name('show');
        Route::put('/update/{id}', [IncomingOfferController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [IncomingOfferController::class, 'destroy'])->name('destroy');

        // Advanced search
        Route::get('/search/advanced', [IncomingOfferController::class, 'search'])->name('search');

        // Form data endpoints
        Route::get('/form-data/get-form-data', [IncomingOfferController::class, 'getFormData'])->name('get-form-data');
        Route::get('/form-data/get-search-form-data', [IncomingOfferController::class, 'getSearchFormData'])->name('get-search-form-data');
        Route::get('/form-data/get-sortable-fields', [IncomingOfferController::class, 'getSortableFields'])->name('get-sortable-fields');

        // Search endpoints
        Route::get('/search/items', [IncomingOfferController::class, 'searchItems'])->name('search-items');
        Route::get('/search/customers', [IncomingOfferController::class, 'searchCustomers'])->name('search-customers');

        // Utility endpoints
        Route::get('/currency/rate', [IncomingOfferController::class, 'getCurrencyRate'])->name('get-currency-rate');
    });

    Route::prefix('outgoing-orders')->as('outgoing-orders.')->group(function () {

        // CRUD operations
        Route::get('/list', [OutgoingOrderController::class, 'index'])->name('index');
        Route::post('/create', [OutgoingOrderController::class, 'store'])->name('store');
        Route::get('/details/{id}', [OutgoingOrderController::class, 'show'])->name('show');
        Route::put('/update/{id}', [OutgoingOrderController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [OutgoingOrderController::class, 'destroy'])->name('destroy');

        // Soft delete management
        Route::get('/deleted/list', [OutgoingOrderController::class, 'getDeleted'])->name('deleted.list');
        Route::post('/{id}/restore', [OutgoingOrderController::class, 'restore'])->name('restore');

        // Helper endpoints
        Route::prefix('/helpers')->as('helpers.')->group(function () {
            Route::get('/customers', [OutgoingOrderController::class, 'getCustomers'])->name('customers');
            Route::get('/items', [OutgoingOrderController::class, 'getItems'])->name('items');
            Route::get('/currencies', [OutgoingOrderController::class, 'getCurrencies'])->name('currencies');
            Route::get('/tax-rates', [OutgoingOrderController::class, 'getTaxRates'])->name('tax-rates');
            Route::get('/live-exchange-rate', [OutgoingOrderController::class, 'getLiveExchangeRate'])->name('live-exchange-rate');
            Route::get('/form-data', [OutgoingOrderController::class, 'getFormData'])->name('form-data');
            Route::get('/search-form-data', [OutgoingOrderController::class, 'getSearchFormData'])->name('search-form-data');
            Route::get('/sortable-fields', [OutgoingOrderController::class, 'getSortableFields'])->name('sortable-fields');
        });
    });

    Route::prefix('incoming-shipments')->as('incoming-shipments.')->group(function () {
        // CRUD operations
        Route::get('/list', [IncomingShipmentController::class, 'index'])->name('index');
        Route::post('/create', [IncomingShipmentController::class, 'store'])->name('store');
        Route::get('/details/{id}', [IncomingShipmentController::class, 'show'])->name('show');
        Route::put('/update/{id}', [IncomingShipmentController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [IncomingShipmentController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('invoices')->as('invoices.')->group(function () {
        // CRUD operations
        // Route::get('/list', [InvoiceController::class, 'index'])->name('index');
        Route::post('/create', [InvoiceController::class, 'store'])->name('store');
        Route::get('/details/{id}', [InvoiceController::class, 'show'])->name('show');
        Route::put('/update/{id}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [InvoiceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('expenses')->as('expenses.')->group(function () {
        // CRUD operations
        Route::get('/list', [ExpenseController::class, 'index'])->name('index');
        Route::post('/create', [ExpenseController::class, 'store'])->name('store');
        Route::get('/details/{id}', [ExpenseController::class, 'show'])->name('show');
        Route::put('/update/{id}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [ExpenseController::class, 'destroy'])->name('destroy');

        // Soft delete management
        Route::get('/deleted/list', [ExpenseController::class, 'getDeleted'])->name('deleted');
        Route::post('/{id}/restore', [ExpenseController::class, 'restore'])->name('restore');

        // Helper endpoints
        Route::prefix('/helpers')->group(function () {
            Route::get('/suppliers', [ExpenseController::class, 'getSuppliers'])->name('suppliers');
            Route::get('/accounts', [ExpenseController::class, 'getAccounts'])->name('accounts');
            Route::get('/currencies', [ExpenseController::class, 'getCurrencies'])->name('currencies');
            Route::get('/tax-rates', [ExpenseController::class, 'getTaxRates'])->name('tax-rates');
            Route::get('/live-exchange-rate', [ExpenseController::class, 'getLiveExchangeRate'])->name('live-exchange-rate');
            Route::get('/form-data', [ExpenseController::class, 'getFormData'])->name('form-data');
            Route::get('/search-form-data', [ExpenseController::class, 'getSearchFormData'])->name('search-form-data');
            Route::get('/sortable-fields', [ExpenseController::class, 'getSortableFields'])->name('sortable-fields');
        });
    });

    // Purchase Reference Invoice routes
    Route::prefix('purchase-reference-invoices')->as('purchase-reference-invoices.')->group(function () {
        // CRUD operations
        Route::get('/list', [PurchaseReferenceInvoiceController::class, 'index'])->name('index');
        Route::post('/create', [PurchaseReferenceInvoiceController::class, 'store'])->name('store');
        Route::post('/debug-create', [PurchaseReferenceInvoiceController::class, 'debugStore'])->name('debug-store');
        Route::get('/details/{id}', [PurchaseReferenceInvoiceController::class, 'show'])->name('show');
        Route::put('/update/{id}', [PurchaseReferenceInvoiceController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [PurchaseReferenceInvoiceController::class, 'destroy'])->name('destroy');

        // Soft delete operations
        Route::get('/deleted/list', [PurchaseReferenceInvoiceController::class, 'getDeleted'])->name('deleted.list');
        Route::post('/{id}/restore', [PurchaseReferenceInvoiceController::class, 'restore'])->name('restore');

        // Helper endpoints
        Route::prefix('/helpers')->as('helpers.')->group(function () {
            Route::get('/suppliers', [PurchaseReferenceInvoiceController::class, 'getSuppliers'])->name('suppliers');
            Route::get('/items', [PurchaseReferenceInvoiceController::class, 'getItems'])->name('items');
            Route::get('/currencies', [PurchaseReferenceInvoiceController::class, 'getCurrencies'])->name('currencies');
            Route::get('/tax-rates', [PurchaseReferenceInvoiceController::class, 'getTaxRates'])->name('tax-rates');
            Route::get('/live-exchange-rate', [PurchaseReferenceInvoiceController::class, 'getLiveExchangeRate'])->name('live-exchange-rate');
            Route::get('/form-data', [PurchaseReferenceInvoiceController::class, 'getFormData'])->name('form-data');
            Route::get('/search-form-data', [PurchaseReferenceInvoiceController::class, 'getSearchFormData'])->name('search-form-data');
            Route::get('/sortable-fields', [PurchaseReferenceInvoiceController::class, 'getSortableFields'])->name('sortable-fields');
        });
    });

    Route::prefix('return-invoices')->as('return-invoices.')->group(function () {
        // CRUD operations
        Route::get('/list', [ReturnInvoiceController::class, 'index'])->name('index');
        Route::post('/create', [ReturnInvoiceController::class, 'store'])->name('store');
        Route::get('/details/{id}', [ReturnInvoiceController::class, 'show'])->name('show');
        Route::put('/update/{id}', [ReturnInvoiceController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [ReturnInvoiceController::class, 'destroy'])->name('destroy');
    });
});
