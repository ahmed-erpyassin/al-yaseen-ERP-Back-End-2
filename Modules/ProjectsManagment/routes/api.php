<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectsManagment\Http\Controllers\ProjectsManagmentController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Project Management Routes
    Route::prefix('projects')->group(function () {
        // Main CRUD operations
        Route::get('/', [ProjectsManagmentController::class, 'index'])->name('projects.index');
        Route::post('/', [ProjectsManagmentController::class, 'store'])->name('projects.store');
        Route::get('/{id}', [ProjectsManagmentController::class, 'show'])->name('projects.show');
        Route::put('/{id}', [ProjectsManagmentController::class, 'update'])->name('projects.update');
        Route::delete('/{id}', [ProjectsManagmentController::class, 'destroy'])->name('projects.destroy');

        // Helper endpoints for dropdown data
        Route::get('/customers/list', [ProjectsManagmentController::class, 'getCustomers'])->name('projects.customers');
        Route::get('/customers/{customerId}/data', [ProjectsManagmentController::class, 'getCustomerData'])->name('projects.customer-data');
        Route::get('/currencies/list', [ProjectsManagmentController::class, 'getCurrencies'])->name('projects.currencies');
        Route::get('/employees/list', [ProjectsManagmentController::class, 'getEmployees'])->name('projects.employees');
        Route::get('/countries/list', [ProjectsManagmentController::class, 'getCountries'])->name('projects.countries');
        Route::get('/statuses/list', [ProjectsManagmentController::class, 'getProjectStatuses'])->name('projects.statuses');

        // Utility endpoints
        Route::post('/calculate-vat', [ProjectsManagmentController::class, 'calculateVAT'])->name('projects.calculate-vat');
        Route::get('/generate-code', [ProjectsManagmentController::class, 'generateProjectCode'])->name('projects.generate-code');
    });
});
