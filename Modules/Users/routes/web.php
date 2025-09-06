<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\AuthController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource('users', UserController::class)->names('users');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
