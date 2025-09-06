<?php

use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Panel\Index;
use App\Livewire\Admin\Panel\Users\UsersList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::prefix('auth/')->middleware(['guest', 'web'])->group(function () {
    Route::get('login', Login::class)->name('login');
});

Route::prefix('admin/')->as('admin.')->middleware(['auth', 'web'])->group(function () {
    Route::prefix('panel/')->as('panel.')->group(function () {
        Route::get('/', Index::class)->name('index');


        Route::prefix('users')->as('users.')->group(function () {
            Route::get('/', UsersList::class)->name('list');
        });


        Route::get('logout', function () {
            Auth::guard('web')->logout();
            return redirect()->route('auth.login');
        })->name('logout');
    });
});

