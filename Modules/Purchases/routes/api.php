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
    Route::prefix('incoming-offers')->group(function () {
        // CRUD operations
        Route::get('/list', [IncomingOfferController::class, 'index'])->name('incoming-offers.index');
        Route::post('/create', [IncomingOfferController::class, 'store'])->name('incoming-offers.store');
        Route::get('/details/{id}', [IncomingOfferController::class, 'show'])->name('incoming-offers.show');
        Route::put('/update/{id}', [IncomingOfferController::class, 'update'])->name('incoming-offers.update');
        Route::delete('/delete/{id}', [IncomingOfferController::class, 'destroy'])->name('incoming-offers.destroy');

        // Advanced search
        Route::get('/search/advanced', [IncomingOfferController::class, 'search'])->name('incoming-offers.search');

        // Form data endpoints
        Route::get('/form-data/get-form-data', [IncomingOfferController::class, 'getFormData'])->name('incoming-offers.get-form-data');
        Route::get('/form-data/get-search-form-data', [IncomingOfferController::class, 'getSearchFormData'])->name('incoming-offers.get-search-form-data');
        Route::get('/form-data/get-sortable-fields', [IncomingOfferController::class, 'getSortableFields'])->name('incoming-offers.get-sortable-fields');

        // Search endpoints
        Route::get('/search/items', [IncomingOfferController::class, 'searchItems'])->name('incoming-offers.search-items');
        Route::get('/search/customers', [IncomingOfferController::class, 'searchCustomers'])->name('incoming-offers.search-customers');

        // Utility endpoints
        Route::get('/currency/rate', [IncomingOfferController::class, 'getCurrencyRate'])->name('incoming-offers.get-currency-rate');
    });
    Route::prefix('outgoing-orders')->group(function () {
        // CRUD operations
        Route::get('/list', [OutgoingOrderController::class, 'index'])->name('outgoing-orders.index');
        Route::post('/create', [OutgoingOrderController::class, 'store'])->name('outgoing-orders.store');
        Route::get('/details/{id}', [OutgoingOrderController::class, 'show'])->name('outgoing-orders.show');
        Route::put('/update/{id}', [OutgoingOrderController::class, 'update'])->name('outgoing-orders.update');
        Route::delete('/delete/{id}', [OutgoingOrderController::class, 'destroy'])->name('outgoing-orders.destroy');

        // Soft delete management
        Route::get('/deleted/list', [OutgoingOrderController::class, 'getDeleted'])->name('outgoing-orders.deleted.list');
        Route::post('/{id}/restore', [OutgoingOrderController::class, 'restore'])->name('outgoing-orders.restore');

        // Helper endpoints
        Route::get('/helpers/customers', [OutgoingOrderController::class, 'getCustomers'])->name('outgoing-orders.helpers.customers');
        Route::get('/helpers/items', [OutgoingOrderController::class, 'getItems'])->name('outgoing-orders.helpers.items');
        Route::get('/helpers/currencies', [OutgoingOrderController::class, 'getCurrencies'])->name('outgoing-orders.helpers.currencies');
        Route::get('/helpers/tax-rates', [OutgoingOrderController::class, 'getTaxRates'])->name('outgoing-orders.helpers.tax-rates');
        Route::get('/helpers/live-exchange-rate', [OutgoingOrderController::class, 'getLiveExchangeRate'])->name('outgoing-orders.helpers.live-exchange-rate');
        Route::get('/helpers/form-data', [OutgoingOrderController::class, 'getFormData'])->name('outgoing-orders.helpers.form-data');
        Route::get('/helpers/search-form-data', [OutgoingOrderController::class, 'getSearchFormData'])->name('outgoing-orders.helpers.search-form-data');
        Route::get('/helpers/sortable-fields', [OutgoingOrderController::class, 'getSortableFields'])->name('outgoing-orders.helpers.sortable-fields');
    });
    Route::prefix('incoming-shipments')->group(function () {
        // CRUD operations
        Route::get('/list', [IncomingShipmentController::class, 'index'])->name('incoming-shipments.index');
        Route::post('/create', [IncomingShipmentController::class, 'store'])->name('incoming-shipments.store');
        Route::get('/details/{id}', [IncomingShipmentController::class, 'show'])->name('incoming-shipments.show');
        Route::put('/update/{id}', [IncomingShipmentController::class, 'update'])->name('incoming-shipments.update');
        Route::delete('/delete/{id}', [IncomingShipmentController::class, 'destroy'])->name('incoming-shipments.destroy');
    });

    Route::prefix('invoices')->group(function () {
        // CRUD operations
        Route::get('/list', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/create', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/details/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::put('/update/{id}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/delete/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });
    Route::prefix('expenses')->group(function () {
        // CRUD operations
        Route::get('/list', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/create', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/details/{id}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::put('/update/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/delete/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        // Soft delete management
        Route::get('/deleted/list', [ExpenseController::class, 'getDeleted'])->name('expenses.deleted');
        Route::post('/{id}/restore', [ExpenseController::class, 'restore'])->name('expenses.restore');

        // Helper endpoints
        Route::get('/helpers/suppliers', [ExpenseController::class, 'getSuppliers'])->name('expenses.suppliers');
        Route::get('/helpers/accounts', [ExpenseController::class, 'getAccounts'])->name('expenses.accounts');
        Route::get('/helpers/currencies', [ExpenseController::class, 'getCurrencies'])->name('expenses.currencies');
        Route::get('/helpers/tax-rates', [ExpenseController::class, 'getTaxRates'])->name('expenses.tax-rates');
        Route::get('/helpers/live-exchange-rate', [ExpenseController::class, 'getLiveExchangeRate'])->name('expenses.live-exchange-rate');
        Route::get('/helpers/form-data', [ExpenseController::class, 'getFormData'])->name('expenses.form-data');
        Route::get('/helpers/search-form-data', [ExpenseController::class, 'getSearchFormData'])->name('expenses.search-form-data');
        Route::get('/helpers/sortable-fields', [ExpenseController::class, 'getSortableFields'])->name('expenses.sortable-fields');
    });

    // Purchase Reference Invoice routes
    Route::prefix('purchase-reference-invoices')->group(function () {
        // CRUD operations
        Route::get('/list', [PurchaseReferenceInvoiceController::class, 'index'])->name('purchase-reference-invoices.index');
        Route::post('/create', [PurchaseReferenceInvoiceController::class, 'store'])->name('purchase-reference-invoices.store');
        Route::post('/debug-create', [PurchaseReferenceInvoiceController::class, 'debugStore'])->name('purchase-reference-invoices.debug-store');
        Route::get('/details/{id}', [PurchaseReferenceInvoiceController::class, 'show'])->name('purchase-reference-invoices.show');
        Route::put('/update/{id}', [PurchaseReferenceInvoiceController::class, 'update'])->name('purchase-reference-invoices.update');
        Route::delete('/delete/{id}', [PurchaseReferenceInvoiceController::class, 'destroy'])->name('purchase-reference-invoices.destroy');

        // Soft delete operations
        Route::get('/deleted/list', [PurchaseReferenceInvoiceController::class, 'getDeleted'])->name('purchase-reference-invoices.deleted.list');
        Route::post('/{id}/restore', [PurchaseReferenceInvoiceController::class, 'restore'])->name('purchase-reference-invoices.restore');

        // Helper endpoints
        Route::get('/helpers/suppliers', [PurchaseReferenceInvoiceController::class, 'getSuppliers'])->name('purchase-reference-invoices.helpers.suppliers');
        Route::get('/helpers/items', [PurchaseReferenceInvoiceController::class, 'getItems'])->name('purchase-reference-invoices.helpers.items');
        Route::get('/helpers/currencies', [PurchaseReferenceInvoiceController::class, 'getCurrencies'])->name('purchase-reference-invoices.helpers.currencies');
        Route::get('/helpers/tax-rates', [PurchaseReferenceInvoiceController::class, 'getTaxRates'])->name('purchase-reference-invoices.helpers.tax-rates');
        Route::get('/helpers/live-exchange-rate', [PurchaseReferenceInvoiceController::class, 'getLiveExchangeRate'])->name('purchase-reference-invoices.helpers.live-exchange-rate');
        Route::get('/helpers/form-data', [PurchaseReferenceInvoiceController::class, 'getFormData'])->name('purchase-reference-invoices.helpers.form-data');
        Route::get('/helpers/search-form-data', [PurchaseReferenceInvoiceController::class, 'getSearchFormData'])->name('purchase-reference-invoices.helpers.search-form-data');
        Route::get('/helpers/sortable-fields', [PurchaseReferenceInvoiceController::class, 'getSortableFields'])->name('purchase-reference-invoices.helpers.sortable-fields');
    });
    Route::prefix('return-invoices')->group(function () {
        // CRUD operations
        Route::get('/list', [ReturnInvoiceController::class, 'index'])->name('return-invoices.index');
        Route::post('/create', [ReturnInvoiceController::class, 'store'])->name('return-invoices.store');
        Route::get('/details/{id}', [ReturnInvoiceController::class, 'show'])->name('return-invoices.show');
        Route::put('/update/{id}', [ReturnInvoiceController::class, 'update'])->name('return-invoices.update');
        Route::delete('/delete/{id}', [ReturnInvoiceController::class, 'destroy'])->name('return-invoices.destroy');
    });
});
