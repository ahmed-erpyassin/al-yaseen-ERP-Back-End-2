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
        Route::get('/', [IncomingOfferController::class, 'index']);
        Route::post('/', [IncomingOfferController::class, 'store']);
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
        Route::get('/', [ExpenseController::class, 'index']);
        Route::post('/', [ExpenseController::class, 'store']);
    });
    Route::prefix('return-invoices')->group(function () {
        Route::get('/', [ReturnInvoiceController::class, 'index']);
        Route::post('/', [ReturnInvoiceController::class, 'store']);
    });
});
