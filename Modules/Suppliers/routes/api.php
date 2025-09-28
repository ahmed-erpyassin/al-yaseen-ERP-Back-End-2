<?php

use Illuminate\Support\Facades\Route;
use Modules\Suppliers\Http\Controllers\SupplierController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('suppliers')->group(function () {
        // Basic CRUD operations
        Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::post('/', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        Route::post('/{supplier}/restore', [SupplierController::class, 'restore'])->name('suppliers.restore');

        // Search and filtering
        Route::get('/search/advanced', [SupplierController::class, 'search'])->name('suppliers.search');

        // Form data endpoints
        Route::get('/form-data/get-form-data', [SupplierController::class, 'getFormData'])->name('suppliers.get-form-data');
        Route::get('/form-data/get-search-form-data', [SupplierController::class, 'getSearchFormData'])->name('suppliers.get-search-form-data');
        Route::get('/form-data/get-sortable-fields', [SupplierController::class, 'getSortableFields'])->name('suppliers.get-sortable-fields');

        // Deleted suppliers management
        Route::get('/deleted/list', [SupplierController::class, 'getDeleted'])->name('suppliers.get-deleted');
        Route::delete('/deleted/{id}/force-delete', [SupplierController::class, 'forceDelete'])->name('suppliers.force-delete');
    });
});
