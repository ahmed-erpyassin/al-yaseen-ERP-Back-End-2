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
        Route::get('/', [OutgoingOrderController::class, 'index']);
        Route::post('/', [OutgoingOrderController::class, 'store']);
    });
    Route::prefix('incoming-shipments')->group(function () {
        // Basic CRUD operations
        Route::get('/', [IncomingShipmentController::class, 'index'])->name('incoming-shipments.index');
        Route::post('/', [IncomingShipmentController::class, 'store'])->name('incoming-shipments.store');
        Route::get('/{id}', [IncomingShipmentController::class, 'show'])->name('incoming-shipments.show');
        Route::put('/{id}', [IncomingShipmentController::class, 'update'])->name('incoming-shipments.update');
        Route::delete('/{id}', [IncomingShipmentController::class, 'destroy'])->name('incoming-shipments.destroy');

        // Advanced search and filtering
        Route::get('/search/advanced', [IncomingShipmentController::class, 'search'])->name('incoming-shipments.search');
        Route::get('/sortable-fields', [IncomingShipmentController::class, 'getSortableFields'])->name('incoming-shipments.sortable-fields');
        Route::get('/sorting-options', [IncomingShipmentController::class, 'getSortingOptions'])->name('incoming-shipments.sorting-options');

        // Soft delete management
        Route::get('/trashed/list', [IncomingShipmentController::class, 'getTrashed'])->name('incoming-shipments.trashed');
        Route::post('/{id}/restore', [IncomingShipmentController::class, 'restore'])->name('incoming-shipments.restore');

        // Form data endpoints
        Route::get('/form-data/get-form-data', [IncomingShipmentController::class, 'getFormData'])->name('incoming-shipments.get-form-data');

        // Search endpoints
        Route::get('/search/customers', [IncomingShipmentController::class, 'searchCustomers'])->name('incoming-shipments.search-customers');
        Route::get('/search/items', [IncomingShipmentController::class, 'searchItems'])->name('incoming-shipments.search-items');
    });
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
    });
    Route::prefix('expenses')->group(function () {
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
    });
    Route::prefix('return-invoices')->group(function () {
        Route::get('/', [ReturnInvoiceController::class, 'index']);
        Route::post('/', [ReturnInvoiceController::class, 'store']);
    });
});
