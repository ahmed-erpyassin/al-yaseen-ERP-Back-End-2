<?php

use Illuminate\Support\Facades\Route;
use Modules\Suppliers\Http\Controllers\SupplierController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');
    });
});
