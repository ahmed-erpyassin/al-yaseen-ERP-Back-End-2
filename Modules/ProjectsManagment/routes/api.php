<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectsManagment\Http\Controllers\ProjectsManagmentController;
use Modules\ProjectsManagment\Http\Controllers\TaskController;
use Modules\ProjectsManagment\Http\Controllers\MilestoneController;
use Modules\ProjectsManagment\Http\Controllers\ResourceController;
use Modules\ProjectsManagment\Http\Controllers\DocumentController;
use Modules\ProjectsManagment\Http\Controllers\ProjectFinancialController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Project Management Routes
    Route::prefix('projects')->group(function () {
        // Main CRUD operations
        Route::get('/', [ProjectsManagmentController::class, 'index'])->name('projects.index');
        Route::post('/', [ProjectsManagmentController::class, 'store'])->name('projects.store');
        Route::get('/{id}', [ProjectsManagmentController::class, 'show'])->name('projects.show');
        Route::put('/{id}', [ProjectsManagmentController::class, 'update'])->name('projects.update');
        Route::delete('/{id}', [ProjectsManagmentController::class, 'destroy'])->name('projects.destroy');

        // Advanced search and filtering
        Route::post('/search', [ProjectsManagmentController::class, 'search'])->name('projects.search');
        Route::get('/filter/by-field', [ProjectsManagmentController::class, 'getProjectsByField'])->name('projects.filter-by-field');
        Route::get('/fields/values', [ProjectsManagmentController::class, 'getFieldValues'])->name('projects.field-values');
        Route::get('/fields/sortable', [ProjectsManagmentController::class, 'getSortableFields'])->name('projects.sortable-fields');
        Route::post('/sort', [ProjectsManagmentController::class, 'sortProjects'])->name('projects.sort');

        // Soft delete management
        Route::post('/{id}/restore', [ProjectsManagmentController::class, 'restore'])->name('projects.restore');
        Route::delete('/{id}/force-delete', [ProjectsManagmentController::class, 'forceDelete'])->name('projects.force-delete');
        Route::get('/trashed/list', [ProjectsManagmentController::class, 'getTrashed'])->name('projects.trashed');

        // Helper endpoints for dropdown data
        Route::get('/customers/list', [ProjectsManagmentController::class, 'getCustomers'])->name('projects.customers');
        Route::get('/customers/{customerId}/data', [ProjectsManagmentController::class, 'getCustomerData'])->name('projects.customer-data');
        Route::get('/currencies/list', [ProjectsManagmentController::class, 'getCurrencies'])->name('projects.currencies');
        Route::get('/employees/list', [ProjectsManagmentController::class, 'getEmployees'])->name('projects.employees');
        Route::get('/countries/list', [ProjectsManagmentController::class, 'getCountries'])->name('projects.countries');
        Route::get('/statuses/list', [ProjectsManagmentController::class, 'getProjectStatuses'])->name('projects.statuses');

        // Utility endpoints
        Route::post('/calculate-vat', [ProjectsManagmentController::class, 'calculateVAT'])->name('projects.calculate-vat');
        Route::get('/generate-code', [ProjectsManagmentController::class, 'generateProjectCode'])->name('projects.generate-code');
    });

    // Task Management Routes
    Route::prefix('tasks')->group(function () {
        // Main CRUD operations
        Route::get('/', [TaskController::class, 'index'])->name('tasks.index');
        Route::post('/', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/{id}', [TaskController::class, 'show'])->name('tasks.show');
        Route::put('/{id}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // Advanced search and filtering
        Route::get('/search/advanced', [TaskController::class, 'search'])->name('tasks.search');
        Route::get('/filter/field', [TaskController::class, 'getTasksByField'])->name('tasks.filter-by-field');

        // Specialized task views
        Route::get('/my-tasks/list', [TaskController::class, 'myTasks'])->name('tasks.my-tasks');
        Route::get('/daily-due/list', [TaskController::class, 'dailyDueTasks'])->name('tasks.daily-due');
        Route::get('/overdue/list', [TaskController::class, 'overdueTasks'])->name('tasks.overdue');

        // Sorting and field management
        Route::get('/fields/sortable', [TaskController::class, 'getSortableFields'])->name('tasks.sortable-fields');
        Route::post('/sort', [TaskController::class, 'sortTasks'])->name('tasks.sort');

        // Helper endpoints for dropdown data
        Route::get('/employees/list', [TaskController::class, 'getEmployees'])->name('tasks.employees');
        Route::get('/statuses/list', [TaskController::class, 'getTaskStatuses'])->name('tasks.statuses');
        Route::get('/priorities/list', [TaskController::class, 'getTaskPriorities'])->name('tasks.priorities');

        // Project-specific tasks
        Route::get('/project/{projectId}', [TaskController::class, 'getProjectTasks'])->name('tasks.project');

        // Document management
        Route::post('/{taskId}/documents', [TaskController::class, 'uploadDocument'])->name('tasks.upload-document');
        Route::get('/{taskId}/documents', [TaskController::class, 'getTaskDocuments'])->name('tasks.documents');
        Route::delete('/documents/{documentId}', [TaskController::class, 'deleteDocument'])->name('tasks.delete-document');
    });

    // Milestone Management Routes
    Route::prefix('milestones')->group(function () {
        // Main CRUD operations
        Route::get('/', [MilestoneController::class, 'index'])->name('milestones.index');
        Route::post('/', [MilestoneController::class, 'store'])->name('milestones.store');
        Route::get('/{id}', [MilestoneController::class, 'show'])->name('milestones.show');
        Route::put('/{id}', [MilestoneController::class, 'update'])->name('milestones.update');
        Route::delete('/{id}', [MilestoneController::class, 'destroy'])->name('milestones.destroy');

        // Advanced search and filtering
        Route::post('/search', [MilestoneController::class, 'search'])->name('milestones.search');
        Route::get('/filter/by-field', [MilestoneController::class, 'getMilestonesByField'])->name('milestones.filter-by-field');
        Route::get('/fields/values', [MilestoneController::class, 'getFieldValues'])->name('milestones.field-values');
        Route::get('/fields/sortable', [MilestoneController::class, 'getSortableFields'])->name('milestones.sortable-fields');
        Route::post('/sort', [MilestoneController::class, 'sortMilestones'])->name('milestones.sort');

        // Soft delete management
        Route::post('/{id}/restore', [MilestoneController::class, 'restore'])->name('milestones.restore');
        Route::delete('/{id}/force-delete', [MilestoneController::class, 'forceDelete'])->name('milestones.force-delete');
        Route::get('/trashed/list', [MilestoneController::class, 'getTrashed'])->name('milestones.trashed');

        // Helper endpoints for dropdown data
        Route::get('/projects/list', [MilestoneController::class, 'getProjects'])->name('milestones.projects');
        Route::get('/statuses/list', [MilestoneController::class, 'getStatusOptions'])->name('milestones.statuses');

        // Utility endpoints
        Route::post('/generate-number', [MilestoneController::class, 'generateMilestoneNumber'])->name('milestones.generate-number');
        Route::get('/project/{projectId}', [MilestoneController::class, 'getProjectMilestones'])->name('milestones.by-project');
    });

    // Resource Management Routes
    Route::prefix('resources')->group(function () {
        // Main CRUD operations
        Route::get('/', [ResourceController::class, 'index'])->name('resources.index');
        Route::post('/', [ResourceController::class, 'store'])->name('resources.store');
        Route::get('/{id}', [ResourceController::class, 'show'])->name('resources.show');
        Route::put('/{id}', [ResourceController::class, 'update'])->name('resources.update');
        Route::delete('/{id}', [ResourceController::class, 'destroy'])->name('resources.destroy');

        // Advanced search and filtering
        Route::post('/search', [ResourceController::class, 'search'])->name('resources.search');
        Route::get('/filter/by-field', [ResourceController::class, 'getResourcesByField'])->name('resources.filter-by-field');
        Route::get('/fields/values', [ResourceController::class, 'getFieldValues'])->name('resources.field-values');
        Route::get('/fields/sortable', [ResourceController::class, 'getSortableFields'])->name('resources.sortable-fields');
        Route::post('/sort', [ResourceController::class, 'sortResources'])->name('resources.sort');

        // Soft delete management
        Route::post('/{id}/restore', [ResourceController::class, 'restore'])->name('resources.restore');
        Route::delete('/{id}/force-delete', [ResourceController::class, 'forceDelete'])->name('resources.force-delete');
        Route::get('/trashed/list', [ResourceController::class, 'getTrashed'])->name('resources.trashed');

        // Helper endpoints for dropdown data
        Route::get('/suppliers/list', [ResourceController::class, 'getSuppliers'])->name('resources.suppliers');
        Route::get('/projects/list', [ResourceController::class, 'getProjects'])->name('resources.projects');
        Route::get('/types/list', [ResourceController::class, 'getResourceTypes'])->name('resources.types');
        Route::get('/statuses/list', [ResourceController::class, 'getStatusOptions'])->name('resources.statuses');

        // Allocation calculation endpoints
        Route::post('/calculate-allocation', [ResourceController::class, 'calculateAllocation'])->name('resources.calculate-allocation');
        Route::post('/calculate-percentage', [ResourceController::class, 'calculateAllocationPercentage'])->name('resources.calculate-percentage');

        // Specialized resource views
        Route::get('/project/{projectId}', [ResourceController::class, 'getProjectResources'])->name('resources.by-project');
        Route::get('/supplier/{supplierId}', [ResourceController::class, 'getSupplierResources'])->name('resources.by-supplier');
    });

    // Document Management Routes
    Route::prefix('documents')->group(function () {
        // Main CRUD operations
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/{id}', [DocumentController::class, 'show'])->name('documents.show');
        Route::put('/{id}', [DocumentController::class, 'update'])->name('documents.update');
        Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        // File operations
        Route::get('/{id}/download', [DocumentController::class, 'downloadDocument'])->name('documents.download');

        // Advanced search and filtering
        Route::post('/search', [DocumentController::class, 'search'])->name('documents.search');
        Route::get('/filter/by-field', [DocumentController::class, 'getDocumentsByField'])->name('documents.filter-by-field');
        Route::get('/fields/values', [DocumentController::class, 'getFieldValues'])->name('documents.field-values');
        Route::get('/fields/sortable', [DocumentController::class, 'getSortableFields'])->name('documents.sortable-fields');
        Route::post('/sort', [DocumentController::class, 'sortDocuments'])->name('documents.sort');

        // Soft delete management
        Route::post('/{id}/restore', [DocumentController::class, 'restore'])->name('documents.restore');
        Route::delete('/{id}/force-delete', [DocumentController::class, 'forceDelete'])->name('documents.force-delete');
        Route::get('/trashed/list', [DocumentController::class, 'getTrashed'])->name('documents.trashed');

        // Helper endpoints for dropdown data
        Route::get('/projects/list', [DocumentController::class, 'getProjects'])->name('documents.projects');
        Route::get('/categories/list', [DocumentController::class, 'getDocumentCategories'])->name('documents.categories');
        Route::get('/statuses/list', [DocumentController::class, 'getStatusOptions'])->name('documents.statuses');

        // Utility endpoints
        Route::post('/generate-number', [DocumentController::class, 'generateDocumentNumber'])->name('documents.generate-number');

        // Specialized document views
        Route::get('/project/{projectId}', [DocumentController::class, 'getProjectDocuments'])->name('documents.by-project');
        Route::get('/category/{category}', [DocumentController::class, 'getDocumentsByCategory'])->name('documents.by-category');
    });

    // Project Financial Management Routes
    Route::prefix('project-financials')->group(function () {
        // Main CRUD operations
        Route::get('/', [ProjectFinancialController::class, 'index'])->name('project-financials.index');
        Route::post('/', [ProjectFinancialController::class, 'store'])->name('project-financials.store');
        Route::get('/{id}', [ProjectFinancialController::class, 'show'])->name('project-financials.show');
        Route::put('/{id}', [ProjectFinancialController::class, 'update'])->name('project-financials.update');
        Route::delete('/{id}', [ProjectFinancialController::class, 'destroy'])->name('project-financials.destroy');

        // Advanced search and filtering
        Route::post('/search', [ProjectFinancialController::class, 'search'])->name('project-financials.search');
        Route::get('/filter/by-field', [ProjectFinancialController::class, 'getProjectFinancialsByField'])->name('project-financials.filter-by-field');
        Route::get('/fields/values', [ProjectFinancialController::class, 'getFieldValues'])->name('project-financials.field-values');
        Route::get('/fields/sortable', [ProjectFinancialController::class, 'getSortableFields'])->name('project-financials.sortable-fields');
        Route::post('/sort', [ProjectFinancialController::class, 'sortProjectFinancials'])->name('project-financials.sort');

        // Soft delete management
        Route::post('/{id}/restore', [ProjectFinancialController::class, 'restore'])->name('project-financials.restore');
        Route::delete('/{id}/force-delete', [ProjectFinancialController::class, 'forceDelete'])->name('project-financials.force-delete');
        Route::get('/trashed/list', [ProjectFinancialController::class, 'getTrashed'])->name('project-financials.trashed');

        // Helper endpoints for dropdown data
        Route::get('/projects/list', [ProjectFinancialController::class, 'getProjects'])->name('project-financials.projects');
        Route::get('/currencies/list', [ProjectFinancialController::class, 'getCurrencies'])->name('project-financials.currencies');

        // Specialized project financial views
        Route::get('/project/{projectId}', [ProjectFinancialController::class, 'getProjectFinancials'])->name('project-financials.by-project');
        Route::get('/reference-type/{referenceType}', [ProjectFinancialController::class, 'getByReferenceType'])->name('project-financials.by-reference-type');
        Route::get('/date-range/{dateFrom}/{dateTo}', [ProjectFinancialController::class, 'getByDateRange'])->name('project-financials.by-date-range');
    });
});
