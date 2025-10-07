<?php

use Illuminate\Support\Facades\Route;
use Modules\Companies\app\Http\Controllers\BusinessTypesController;
use Modules\Companies\app\Http\Controllers\CountriesController;
use Modules\Companies\app\Http\Controllers\IndustriesController;
use Modules\Companies\Http\Controllers\BranchesController;
use Modules\Companies\Http\Controllers\CitiesController;
use Modules\Companies\Http\Controllers\CompaniesController;
use Modules\Companies\Http\Controllers\RegionsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::withoutMiddleware(['auth:sanctum'])->group(function () {
        Route::apiResource('countries', CountriesController::class)->names('countries');
        Route::apiResource('regions', RegionsController::class)->names('regions');
        Route::apiResource('cities', CitiesController::class)->names('cities');

        Route::apiResource('industries', IndustriesController::class)->names('industries');
        Route::apiResource('business-types', BusinessTypesController::class)->names('business_types');

        Route::apiResource('companies', CompaniesController::class)->names('companies');
        Route::apiResource('branches', BranchesController::class)->names('branches');
    });
});
