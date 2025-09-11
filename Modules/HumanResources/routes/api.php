<?php

use Illuminate\Support\Facades\Route;
use Modules\HumanResources\Http\Controllers\AttendanceRecordController;
use Modules\HumanResources\Http\Controllers\DepartmentController;
use Modules\HumanResources\Http\Controllers\EmployeeController;
use Modules\HumanResources\Http\Controllers\HumanResourcesController;
use Modules\HumanResources\Http\Controllers\LeaveRequestController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index']);
        Route::post('/', [DepartmentController::class, 'store']);
        Route::put('/{department}', [DepartmentController::class, 'update']);
        Route::delete('/{department}', [DepartmentController::class, 'destroy']);
    });
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/{employee}', [EmployeeController::class, 'destroy']);
    });
    Route::prefix('leave_requests')->group(function () {
        Route::get('/', [LeaveRequestController::class, 'index']);
        Route::post('/', [LeaveRequestController::class, 'store']);
        Route::put('/{leaveRequest}', [LeaveRequestController::class, 'update']);
        Route::delete('/{leaveRequest}', [LeaveRequestController::class, 'destroy']);
    });
    Route::prefix('attendances')->group(function () {
        Route::get('/', [AttendanceRecordController::class, 'index']);
        Route::post('/', [AttendanceRecordController::class, 'store']);
        Route::put('/{attendance}', [AttendanceRecordController::class, 'update']);
        Route::delete('/{attendance}', [AttendanceRecordController::class, 'destroy']);
    });
});
