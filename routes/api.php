<?php

use App\Http\Controllers\Accounting\BudgetController;
use App\Http\Controllers\Accounting\DepartmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\Company\FunderController;
use App\Http\Controllers\CompanyTypeController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\Projects\ProjectController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\WorkTypeController;
use App\Models\CompanyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// Start Auth

Route::group(['prefix' => 'auth'], function () {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forget-password', [AuthController::class, 'forgotPassword']);
    Route::post('/check-otp', [AuthController::class, 'checkOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/create-company', [RegisterCompanyController::class, 'store'])->middleware('auth:sanctum');
});

Route::get('currency/all', [CurrencyController::class, 'all']);
Route::get('work-types/all', [WorkTypeController::class, 'all']);
Route::get('company-types/all', [CompanyTypeController::class, 'all']);
Route::get('country/all', [CountryController::class, 'index']);


Route::group(['prefix' => 'sales', 'middleware' => 'auth:sanctum'], function () {

    Route::group(['prefix' => 'quotations'], function () {

        Route::get('/all', [QuotationController::class, 'index']);
        Route::post('/create', [QuotationController::class, 'store']);
    });
});
Route::group(['prefix' => 'accounting', 'middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'funders', 'middleware' => 'auth:sanctum'], function () {

        Route::get('/all', [FunderController::class, 'index']);
        Route::post('/create', [FunderController::class, 'create']);
    });
    Route::group(['prefix' => 'budget'], function () {

        Route::get('/all', [BudgetController::class, 'index']);
        Route::post('/create', [BudgetController::class, 'store']);
    });
    Route::group(['prefix' => 'departments'], function () {

        Route::get('/all', [DepartmentController::class, 'index']);
        Route::post('/create', [DepartmentController::class, 'store']);
    });
});

Route::group(['prefix' => 'projects', 'middleware' => 'auth:sanctum'], function () {

    Route::get('/all', [ProjectController::class, 'index']);
    Route::post('/create', [ProjectController::class, 'store']);
});
