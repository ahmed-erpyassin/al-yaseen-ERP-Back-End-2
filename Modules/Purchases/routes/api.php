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
        Route::get('/', [OutgoingOrderController::class, 'index']);
        Route::post('/', [OutgoingOrderController::class, 'store']);
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
