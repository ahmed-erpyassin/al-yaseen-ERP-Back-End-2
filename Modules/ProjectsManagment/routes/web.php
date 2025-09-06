<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectsManagment\Http\Controllers\ProjectsManagmentController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Route::resource('projectsmanagments', ProjectsManagmentController::class)->names('projectsmanagment');
});
