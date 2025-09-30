<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchases\Http\Controllers\ExpenseController;
use Modules\Purchases\Http\Controllers\IncomingOfferController;
use Modules\Purchases\Http\Controllers\IncomingShipmentController;
use Modules\Purchases\Http\Controllers\InvoiceController;
use Modules\Purchases\Http\Controllers\OutgoingOrderController;
use Modules\Purchases\Http\Controllers\ReturnInvoiceController;

Route::middleware(['auth:sanctum'])->prefix('v1/purchase')->group(function () {
    Route::prefix('incoming-offers')->group(function () {
        // CRUD operations
        Route::get('/', [IncomingOfferController::class, 'index'])->name('incoming-offers.index');
        Route::post('/', [IncomingOfferController::class, 'store'])->name('incoming-offers.store');
        Route::get('/{id}', [IncomingOfferController::class, 'show'])->name('incoming-offers.show');
        Route::put('/{id}', [IncomingOfferController::class, 'update'])->name('incoming-offers.update');
        Route::delete('/{id}', [IncomingOfferController::class, 'destroy'])->name('incoming-offers.destroy');

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
        Route::get('/', [OutgoingOrderController::class, 'index']);
        Route::post('/', [OutgoingOrderController::class, 'store']);
        Route::get('/{id}', [OutgoingOrderController::class, 'show']);
        Route::put('/{id}', [OutgoingOrderController::class, 'update']);
        Route::delete('/{id}', [OutgoingOrderController::class, 'destroy']);

        // Soft delete management
        Route::get('/deleted/list', [OutgoingOrderController::class, 'getDeleted']);
        Route::post('/{id}/restore', [OutgoingOrderController::class, 'restore']);

        // Helper endpoints
        Route::get('/helpers/customers', [OutgoingOrderController::class, 'getCustomers']);
        Route::get('/helpers/items', [OutgoingOrderController::class, 'getItems']);
        Route::get('/helpers/currencies', [OutgoingOrderController::class, 'getCurrencies']);
        Route::get('/helpers/tax-rates', [OutgoingOrderController::class, 'getTaxRates']);
        Route::get('/helpers/live-exchange-rate', [OutgoingOrderController::class, 'getLiveExchangeRate']);
        Route::get('/helpers/form-data', [OutgoingOrderController::class, 'getFormData']);
        Route::get('/helpers/search-form-data', [OutgoingOrderController::class, 'getSearchFormData']);
        Route::get('/helpers/sortable-fields', [OutgoingOrderController::class, 'getSortableFields']);
    });
    Route::prefix('incoming-shipments')->group(function () {
        Route::get('/', [IncomingShipmentController::class, 'index']);
        Route::post('/', [IncomingShipmentController::class, 'store']);
    });
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
    });
    Route::prefix('expenses')->group(function () {
        // CRUD operations
        Route::get('/', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/{id}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::put('/{id}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

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
    Route::prefix('return-invoices')->group(function () {
        Route::get('/', [ReturnInvoiceController::class, 'index']);
        Route::post('/', [ReturnInvoiceController::class, 'store']);
    });
});
