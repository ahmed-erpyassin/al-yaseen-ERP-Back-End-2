<?php

use Illuminate\Support\Facades\Route;
use Modules\Billing\Http\Controllers\InvoiceController;
use Modules\Billing\Http\Controllers\InvoicePaymentController;
use Modules\Billing\Http\Controllers\InvoiceTaxController;
use Modules\Billing\Http\Controllers\JournalController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::apiResource('', InvoiceController::class);
        Route::post('/{id}/approve', [InvoiceController::class, 'approve']); // اعتماد الفاتورة (ترحيل مالي)
    });

    Route::prefix('journals')->name('journals.')->group(function () {
        Route::apiResource('', JournalController::class);
    });

    Route::prefix('invoice-payments')->name('invoice-payments.')->group(function () {
        Route::apiResource('', InvoicePaymentController::class);
    });

    Route::prefix('invoice-taxes')->name('invoice-taxes.')->group(function () {
        Route::apiResource('', InvoiceTaxController::class);
    });
});
