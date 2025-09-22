<?php

use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Panel\Companies\CompaniesList;
use App\Livewire\Admin\Panel\Companies\ShowCompany;
use App\Livewire\Admin\Panel\FinancialAccounts\CurrenciesList;
use App\Livewire\Admin\Panel\FinancialAccounts\ExchangeRatesList;
use App\Livewire\Admin\Panel\FinancialAccounts\FiscalYearsList;
use App\Livewire\Admin\Panel\Index;
use App\Livewire\Admin\Panel\Roles\CreateRole;
use App\Livewire\Admin\Panel\Roles\EditRole;
use App\Livewire\Admin\Panel\Roles\RolesList;
use App\Livewire\Admin\Panel\Users\ManageUserRoles;
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
            Route::get('manage-user-roles', ManageUserRoles::class)->name('manage-user-roles');

            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('', RolesList::class)->name('list');
                Route::get('create', CreateRole::class)->name('create');
                Route::get('edit', EditRole::class)->name('edit');
            });
        });

        Route::prefix('companies')->as('companies.')->group(function () {
            Route::get('/', CompaniesList::class)->name('list');
            Route::get('/show', ShowCompany::class)->name('show');
        });

        Route::prefix('financial-accounts')->as('financial-accounts.')->group(function () {

            // Currencies
            Route::prefix('currencies')->as('currencies.')->group(function () {
                Route::get('/', CurrenciesList::class)->name('list');
            });

            // Exchange Rates
            Route::prefix('exchange-rates')->as('exchange-rates.')->group(function () {
                Route::get('/', ExchangeRatesList::class)->name('list');
            });

            // Fiscal Years
            Route::prefix('fiscal-years')->as('fiscal-years.')->group(function () {
                Route::get('/', FiscalYearsList::class)->name('list');
            });
        });



        Route::get('logout', function () {
            Auth::guard('web')->logout();
            return redirect()->route('auth.login');
        })->name('logout');
    });
});
