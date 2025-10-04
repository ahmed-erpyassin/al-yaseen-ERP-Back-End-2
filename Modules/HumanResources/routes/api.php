<?php

use Illuminate\Support\Facades\Route;
use Modules\HumanResources\Http\Controllers\AttendanceRecordController;
use Modules\HumanResources\Http\Controllers\DepartmentController;
use Modules\HumanResources\Http\Controllers\EmployeeController;
use Modules\HumanResources\Http\Controllers\Employee\EmployeeController as NewEmployeeController;
use Modules\HumanResources\Http\Controllers\Employee\EmployeeSearchController;
use Modules\HumanResources\Http\Controllers\Employee\CurrencyRateController;
use Modules\HumanResources\Http\Controllers\HumanResourcesController;
use Modules\HumanResources\Http\Controllers\LeaveRequestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Departments Routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/list', [DepartmentController::class, 'index'])->name('list');
        Route::post('/create', [DepartmentController::class, 'store'])->name('create');
        Route::put('/{department}/update', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}/delete', [DepartmentController::class, 'destroy'])->name('delete');
    });

    // Enhanced Employee Routes
    Route::prefix('employees')->name('employees.')->group(function () {
        // Basic CRUD operations
        Route::get('/list', [NewEmployeeController::class, 'index'])->name('list');
        Route::post('/create', [NewEmployeeController::class, 'store'])->name('create');
        Route::get('/{employee}/show', [NewEmployeeController::class, 'show'])->name('show');
        Route::put('/{employee}/update', [NewEmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}/delete', [NewEmployeeController::class, 'destroy'])->name('delete');

        // Helper endpoints
        Route::get('/form-data/get', [NewEmployeeController::class, 'getFormData'])->name('form-data');
        Route::get('/next-number/generate', [NewEmployeeController::class, 'getNextEmployeeNumber'])->name('next-number');

        // Advanced search endpoints
        Route::prefix('search')->name('search.')->group(function () {
            Route::post('/advanced', [NewEmployeeController::class, 'search'])->name('advanced');
            Route::post('/quick', [EmployeeSearchController::class, 'quickSearch'])->name('quick');
            Route::get('/form-data', [EmployeeSearchController::class, 'getSearchFormData'])->name('form-data');
            Route::get('/statistics', [EmployeeSearchController::class, 'getStatistics'])->name('statistics');
            Route::post('/export', [EmployeeSearchController::class, 'exportEmployees'])->name('export');
        });

        // Soft delete management
        Route::prefix('deleted')->name('deleted.')->group(function () {
            Route::get('/list', [NewEmployeeController::class, 'deleted'])->name('list');
            Route::post('/{employeeId}/restore', [NewEmployeeController::class, 'restore'])->name('restore');
        });

        // Currency rate endpoints
        Route::prefix('currency-rates')->name('currency-rates.')->group(function () {
            Route::post('/live-rate', [CurrencyRateController::class, 'getLiveRate'])->name('live-rate');
            Route::post('/live-rates', [CurrencyRateController::class, 'getLiveRates'])->name('live-rates');
            Route::put('/update-rate', [CurrencyRateController::class, 'updateRate'])->name('update-rate');
        });
    });

    // Legacy Employee Routes (keeping for backward compatibility)
    Route::prefix('employees-legacy')->name('employees-legacy.')->group(function () {
        Route::get('/list', [EmployeeController::class, 'index'])->name('list');
        Route::post('/create', [EmployeeController::class, 'store'])->name('create');
        Route::put('/{employee}/update', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}/delete', [EmployeeController::class, 'destroy'])->name('delete');
    });

    // Leave Requests Routes
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/list', [LeaveRequestController::class, 'index'])->name('list');
        Route::post('/create', [LeaveRequestController::class, 'store'])->name('create');
        Route::put('/{leaveRequest}/update', [LeaveRequestController::class, 'update'])->name('update');
        Route::delete('/{leaveRequest}/delete', [LeaveRequestController::class, 'destroy'])->name('delete');
    });

    // Attendance Records Routes
    Route::prefix('attendances')->name('attendances.')->group(function () {
        Route::get('/list', [AttendanceRecordController::class, 'index'])->name('list');
        Route::post('/create', [AttendanceRecordController::class, 'store'])->name('create');
        Route::put('/{attendance}/update', [AttendanceRecordController::class, 'update'])->name('update');
        Route::delete('/{attendance}/delete', [AttendanceRecordController::class, 'destroy'])->name('delete');
    });
});
