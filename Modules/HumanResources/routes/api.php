<?php

use Illuminate\Support\Facades\Route;
use Modules\HumanResources\Http\Controllers\AttendanceRecordController;
use Modules\HumanResources\Http\Controllers\DepartmentController;
use Modules\HumanResources\Http\Controllers\Employee\EmployeeController;
use Modules\HumanResources\Http\Controllers\Employee\EmployeeSearchController;
use Modules\HumanResources\Http\Controllers\Employee\CurrencyRateController;
use Modules\HumanResources\Http\Controllers\Employee\PayrollController;
use Modules\HumanResources\Http\Controllers\Employee\PayrollDataController;
use Modules\HumanResources\Http\Controllers\Employee\PayrollSearchController;
use Modules\HumanResources\Http\Controllers\HumanResourcesController;
use Modules\HumanResources\Http\Controllers\LeaveRequestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    // Departments Routes
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/list', [DepartmentController::class, 'index'])->name('list');
        Route::get('/first', [DepartmentController::class, 'first'])->name('first');
        Route::get('/{department}/show', [DepartmentController::class, 'show'])->name('show');
        Route::post('/create', [DepartmentController::class, 'store'])->name('create');
        Route::put('/{department}/update', [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{department}/delete', [DepartmentController::class, 'destroy'])->name('delete');
        Route::get('/next-number/generate', [DepartmentController::class, 'getNextDepartmentNumber'])->name('next-number');
    });

    // Enhanced Employee Routes
    Route::prefix('employees')->name('employees.')->group(function () {
        // Basic CRUD operations
        Route::get('/list', [EmployeeController::class, 'index'])->name('list');
        Route::post('/create', [EmployeeController::class, 'store'])->name('create');
        Route::get('/{employee}/show', [EmployeeController::class, 'show'])->name('show');
        Route::put('/{employee}/update', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}/delete', [EmployeeController::class, 'destroy'])->name('delete');

        // Helper endpoints
        Route::get('/form-data/get', [EmployeeController::class, 'getFormData'])->name('form-data');
        Route::get('/next-number/generate', [EmployeeController::class, 'getNextEmployeeNumber'])->name('next-number');

        // Advanced search endpoints
        Route::prefix('search')->name('search.')->group(function () {
            Route::post('/advanced', [EmployeeController::class, 'search'])->name('advanced');
            Route::post('/quick', [EmployeeSearchController::class, 'quickSearch'])->name('quick');
            Route::get('/form-data', [EmployeeSearchController::class, 'getSearchFormData'])->name('form-data');
            Route::get('/statistics', [EmployeeSearchController::class, 'getStatistics'])->name('statistics');
            Route::post('/export', [EmployeeSearchController::class, 'exportEmployees'])->name('export');
        });

        // Soft delete management
        Route::prefix('deleted')->name('deleted.')->group(function () {
            Route::get('/list', [EmployeeController::class, 'deleted'])->name('list');
            Route::post('/{employeeId}/restore', [EmployeeController::class, 'restore'])->name('restore');
        });

        // Currency rate endpoints
        Route::prefix('currency-rates')->name('currency-rates.')->group(function () {
            Route::post('/live-rate', [CurrencyRateController::class, 'getLiveRate'])->name('live-rate');
            Route::post('/live-rates', [CurrencyRateController::class, 'getLiveRates'])->name('live-rates');
            Route::put('/update-rate', [CurrencyRateController::class, 'updateRate'])->name('update-rate');
        });

        // Payroll Routes
        Route::prefix('payroll')->name('payroll.')->group(function () {
            // Payroll Records CRUD
            Route::get('/records/list', [PayrollController::class, 'index'])->name('records.list');
            Route::post('/records/create', [PayrollController::class, 'store'])->name('records.create');
            Route::get('/records/{payrollRecord}/show', [PayrollController::class, 'show'])->name('records.show');
            Route::put('/records/{payrollRecord}/update', [PayrollController::class, 'update'])->name('records.update');
            Route::delete('/records/{payrollRecord}/delete', [PayrollController::class, 'destroy'])->name('records.delete');

            // Payroll Record Helper Endpoints
            Route::get('/records/{payrollRecord}/totals', [PayrollController::class, 'getWithTotals'])->name('records.totals');
            Route::post('/records/{payrollRecord}/recalculate', [PayrollController::class, 'recalculateTotals'])->name('records.recalculate');
            Route::post('/records/generate-number', [PayrollController::class, 'generatePayrollNumber'])->name('records.generate-number');
            Route::get('/records/statistics', [PayrollController::class, 'getStatistics'])->name('records.statistics');

            // Review and Preview Endpoints
            Route::get('/records/{payrollRecord}/preview', [PayrollController::class, 'preview'])->name('records.preview');
            Route::get('/records/{payrollRecord}/review', [PayrollController::class, 'review'])->name('records.review');
            Route::get('/records/{payrollRecord}/all-data', [PayrollController::class, 'getAllData'])->name('records.all-data');

            // Sorting and Navigation Endpoints
            Route::get('/records/sorted', [PayrollController::class, 'getSorted'])->name('records.sorted');
            Route::get('/records/first-last', [PayrollController::class, 'getFirstLast'])->name('records.first-last');

            // Soft Delete Management
            Route::get('/records/deleted', [PayrollController::class, 'deleted'])->name('records.deleted');
            Route::post('/records/{payrollRecordId}/restore', [PayrollController::class, 'restore'])->name('records.restore');
            Route::delete('/records/{payrollRecordId}/force-delete', [PayrollController::class, 'forceDelete'])->name('records.force-delete');

            // Payroll Data CRUD (nested under payroll records)
            Route::prefix('records/{payrollRecord}/data')->name('data.')->group(function () {
                Route::get('/list', [PayrollDataController::class, 'index'])->name('list');
                Route::post('/create', [PayrollDataController::class, 'store'])->name('create');
                Route::get('/{payrollData}/show', [PayrollDataController::class, 'show'])->name('show');
                Route::put('/{payrollData}/update', [PayrollDataController::class, 'update'])->name('update');
                Route::delete('/{payrollData}/delete', [PayrollDataController::class, 'destroy'])->name('delete');

                // Payroll Data Helper Endpoints
                Route::post('/populate-from-employee', [PayrollDataController::class, 'populateFromEmployee'])->name('populate-employee');
                Route::post('/{payrollData}/recalculate', [PayrollDataController::class, 'recalculateAmounts'])->name('recalculate');
                Route::post('/bulk-add-employees', [PayrollDataController::class, 'bulkAddEmployees'])->name('bulk-add');

                // Sorting and Navigation Endpoints
                Route::get('/sorted', [PayrollDataController::class, 'getSorted'])->name('sorted');
                Route::get('/first-last', [PayrollDataController::class, 'getFirstLast'])->name('first-last');

                // Soft Delete Management
                Route::post('/{payrollDataId}/restore', [PayrollDataController::class, 'restore'])->name('restore');
                Route::delete('/{payrollDataId}/force-delete', [PayrollDataController::class, 'forceDelete'])->name('force-delete');
            });

            // Payroll Search and Autocomplete Endpoints
            Route::prefix('search')->name('search.')->group(function () {
                Route::get('/employees', [PayrollSearchController::class, 'searchEmployees'])->name('employees');
                Route::get('/employee-by-number', [PayrollSearchController::class, 'getEmployeeByNumber'])->name('employee-by-number');
                Route::get('/accounts', [PayrollSearchController::class, 'searchAccounts'])->name('accounts');
                Route::get('/account-by-code', [PayrollSearchController::class, 'getAccountByCode'])->name('account-by-code');
                Route::get('/currencies', [PayrollSearchController::class, 'getCurrencies'])->name('currencies');
                Route::get('/employee-numbers', [PayrollSearchController::class, 'getEmployeeNumbers'])->name('employee-numbers');
                Route::get('/account-numbers', [PayrollSearchController::class, 'getAccountNumbers'])->name('account-numbers');

                // Employee Selection Endpoints
                Route::get('/employee-selection-options', [PayrollSearchController::class, 'getEmployeeSelectionOptions'])->name('employee-selection-options');
                Route::get('/payroll-data-by-selection', [PayrollSearchController::class, 'getPayrollDataByEmployeeSelection'])->name('payroll-data-by-selection');
            });
        });
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
