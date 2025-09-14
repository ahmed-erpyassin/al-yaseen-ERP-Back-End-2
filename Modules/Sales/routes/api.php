<?php

use Modules\Sales\Http\Controllers\IncomingOrderController;
use Modules\Sales\Http\Controllers\OutgoingShipmentController;
use Modules\Sales\Http\Controllers\ReturnInvoiceController;
use Modules\Sales\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\InvoiceController;
use Modules\Sales\Http\Controllers\OutgoingOfferController;

Route::middleware(['auth:sanctum'])->prefix('v1/sales')->group(function () {

    Route::prefix('outgoing-offers')->group(function () {
        Route::get('/', [OutgoingOfferController::class, 'index']);
        Route::post('/', [OutgoingOfferController::class, 'store']);
    });
    Route::prefix('outgoing-shipments')->group(function () {
        Route::get('/', [OutgoingShipmentController::class, 'index']);
        Route::post('/', [OutgoingShipmentController::class, 'store']);
    });
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::post('/', [InvoiceController::class, 'store']);
    });
    Route::prefix('return-invoices')->group(function () {
        Route::get('/', [ReturnInvoiceController::class, 'index']);
        Route::post('/', [ReturnInvoiceController::class, 'store']);
    });
    Route::prefix('incoming-orders')->group(function () {
        Route::get('/', [IncomingOrderController::class, 'index']);
        Route::post('/', [IncomingOrderController::class, 'store']);
    });
    Route::prefix('services')->group(function () {
        Route::get('/', [ServiceController::class, 'index']);
        Route::post('/', [ServiceController::class, 'store']);
    });

});
