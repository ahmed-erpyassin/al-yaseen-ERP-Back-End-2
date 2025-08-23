<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegisterCompanyController;
use App\Http\Controllers\CompanyTypeController;
use App\Http\Controllers\CurrencyController;
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
