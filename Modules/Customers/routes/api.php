<?php

use Illuminate\Support\Facades\Route;
use Modules\Customers\Http\Controllers\CustomerController;
use Modules\Customers\Http\Controllers\CustomersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::put('/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('/{customer}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    });
});
