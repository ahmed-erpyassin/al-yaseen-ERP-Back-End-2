<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectsManagment\Http\Controllers\ProjectsManagmentController;
use Modules\ProjectsManagment\Http\Controllers\TaskController;
use Modules\ProjectsManagment\Http\Controllers\MilestoneController;
use Modules\ProjectsManagment\Http\Controllers\ResourceController;
use Modules\ProjectsManagment\Http\Controllers\DocumentController;
use Modules\ProjectsManagment\Http\Controllers\ProjectFinancialController;
use Modules\ProjectsManagment\Http\Controllers\ProjectRiskController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Project Management Routes
    Route::prefix('projects')->group(function () {
        // Main CRUD operations
        Route::get('/browse-all', [ProjectsManagmentController::class, 'index'])->name('project-mgmt.projects.browse-all');
        Route::post('/establish-new', [ProjectsManagmentController::class, 'store'])->name('project-mgmt.projects.establish-new');
        Route::get('/examine/{id}', [ProjectsManagmentController::class, 'show'])->name('project-mgmt.projects.examine');
        Route::put('/revise/{id}', [ProjectsManagmentController::class, 'update'])->name('project-mgmt.projects.revise');
        Route::delete('/eliminate/{id}', [ProjectsManagmentController::class, 'destroy'])->name('project-mgmt.projects.eliminate');

        // Advanced search and filtering
        Route::post('/advanced-search', [ProjectsManagmentController::class, 'search'])->name('project-mgmt.projects.advanced-search');
        Route::get('/filter-by-field', [ProjectsManagmentController::class, 'getProjectsByField'])->name('project-mgmt.projects.filter-by-field');
        Route::get('/field-values', [ProjectsManagmentController::class, 'getFieldValues'])->name('project-mgmt.projects.field-values');
        Route::get('/sortable-fields', [ProjectsManagmentController::class, 'getSortableFields'])->name('project-mgmt.projects.sortable-fields');
        Route::post('/apply-sort', [ProjectsManagmentController::class, 'sortProjects'])->name('project-mgmt.projects.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-project', [ProjectsManagmentController::class, 'restore'])->name('project-mgmt.projects.restore-project');
        Route::delete('/{id}/permanent-delete', [ProjectsManagmentController::class, 'forceDelete'])->name('project-mgmt.projects.permanent-delete');
        Route::get('/deleted-projects', [ProjectsManagmentController::class, 'getTrashed'])->name('project-mgmt.projects.deleted-projects');

        // Helper endpoints for dropdown data
        Route::get('/customer-options', [ProjectsManagmentController::class, 'getCustomers'])->name('project-mgmt.projects.customer-options');
        Route::get('/customer-details/{customerId}', [ProjectsManagmentController::class, 'getCustomerData'])->name('project-mgmt.projects.customer-details');
        Route::get('/currency-options', [ProjectsManagmentController::class, 'getCurrencies'])->name('project-mgmt.projects.currency-options');
        Route::get('/employee-options', [ProjectsManagmentController::class, 'getEmployees'])->name('project-mgmt.projects.employee-options');
        Route::get('/country-options', [ProjectsManagmentController::class, 'getCountries'])->name('project-mgmt.projects.country-options');
        Route::get('/status-options', [ProjectsManagmentController::class, 'getProjectStatuses'])->name('project-mgmt.projects.status-options');

        // Utility endpoints
        Route::post('/vat-calculation', [ProjectsManagmentController::class, 'calculateVAT'])->name('project-mgmt.projects.vat-calculation');
        Route::get('/code-generation', [ProjectsManagmentController::class, 'generateProjectCode'])->name('project-mgmt.projects.code-generation');
    });

    // Task Management Routes
    Route::prefix('project-tasks')->group(function () {
        // Main CRUD operations
        Route::get('/fetch-all', [TaskController::class, 'index'])->name('project-mgmt.tasks.fetch-all');
        Route::post('/generate-new', [TaskController::class, 'store'])->name('project-mgmt.tasks.generate-new');
        Route::get('/inspect/{id}', [TaskController::class, 'show'])->name('project-mgmt.tasks.inspect');
        Route::put('/update-existing/{id}', [TaskController::class, 'update'])->name('project-mgmt.tasks.update-existing');
        Route::delete('/delete-item/{id}', [TaskController::class, 'destroy'])->name('project-mgmt.tasks.delete-item');

        // Advanced search and filtering
        Route::get('/advanced-search', [TaskController::class, 'search'])->name('project-mgmt.tasks.advanced-search');
        Route::get('/filter-by-field', [TaskController::class, 'getTasksByField'])->name('project-mgmt.tasks.filter-by-field');

        // Specialized task views
        Route::get('/assigned-to-me', [TaskController::class, 'myTasks'])->name('project-mgmt.tasks.assigned-to-me');
        Route::get('/due-today', [TaskController::class, 'dailyDueTasks'])->name('project-mgmt.tasks.due-today');
        Route::get('/overdue-tasks', [TaskController::class, 'overdueTasks'])->name('project-mgmt.tasks.overdue-tasks');

        // Sorting and field management
        Route::get('/sortable-fields', [TaskController::class, 'getSortableFields'])->name('project-mgmt.tasks.sortable-fields');
        Route::post('/apply-sort', [TaskController::class, 'sortTasks'])->name('project-mgmt.tasks.apply-sort');

        // Helper endpoints for dropdown data
        Route::get('/employee-options', [TaskController::class, 'getEmployees'])->name('project-mgmt.tasks.employee-options');
        Route::get('/status-options', [TaskController::class, 'getTaskStatuses'])->name('project-mgmt.tasks.status-options');
        Route::get('/priority-options', [TaskController::class, 'getTaskPriorities'])->name('project-mgmt.tasks.priority-options');

        // Project-specific tasks
        Route::get('/by-project/{projectId}', [TaskController::class, 'getProjectTasks'])->name('project-mgmt.tasks.by-project');

        // Document management
        Route::post('/{taskId}/upload-document', [TaskController::class, 'uploadDocument'])->name('project-mgmt.tasks.upload-document');
        Route::get('/{taskId}/task-documents', [TaskController::class, 'getTaskDocuments'])->name('project-mgmt.tasks.task-documents');
        Route::delete('/document/{documentId}/remove', [TaskController::class, 'deleteDocument'])->name('project-mgmt.tasks.remove-document');
    });

    // Milestone Management Routes
    Route::prefix('project-milestones')->group(function () {
        // Main CRUD operations
        Route::get('/retrieve-all', [MilestoneController::class, 'index'])->name('project-mgmt.milestones.retrieve-all');
        Route::post('/build-new', [MilestoneController::class, 'store'])->name('project-mgmt.milestones.build-new');
        Route::get('/view-single/{id}', [MilestoneController::class, 'show'])->name('project-mgmt.milestones.view-single');
        Route::put('/edit-record/{id}', [MilestoneController::class, 'update'])->name('project-mgmt.milestones.edit-record');
        Route::delete('/destroy-entry/{id}', [MilestoneController::class, 'destroy'])->name('project-mgmt.milestones.destroy-entry');

        // Advanced search and filtering
        Route::post('/advanced-search', [MilestoneController::class, 'search'])->name('project-mgmt.milestones.advanced-search');
        Route::get('/filter-by-field', [MilestoneController::class, 'getMilestonesByField'])->name('project-mgmt.milestones.filter-by-field');
        Route::get('/field-values', [MilestoneController::class, 'getFieldValues'])->name('project-mgmt.milestones.field-values');
        Route::get('/sortable-fields', [MilestoneController::class, 'getSortableFields'])->name('project-mgmt.milestones.sortable-fields');
        Route::post('/apply-sort', [MilestoneController::class, 'sortMilestones'])->name('project-mgmt.milestones.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-milestone', [MilestoneController::class, 'restore'])->name('project-mgmt.milestones.restore-milestone');
        Route::delete('/{id}/permanent-delete', [MilestoneController::class, 'forceDelete'])->name('project-mgmt.milestones.permanent-delete');
        Route::get('/deleted-milestones', [MilestoneController::class, 'getTrashed'])->name('project-mgmt.milestones.deleted-milestones');

        // Helper endpoints for dropdown data
        Route::get('/project-options', [MilestoneController::class, 'getProjects'])->name('project-mgmt.milestones.project-options');
        Route::get('/status-options', [MilestoneController::class, 'getStatusOptions'])->name('project-mgmt.milestones.status-options');

        // Utility endpoints
        Route::post('/number-generation', [MilestoneController::class, 'generateMilestoneNumber'])->name('project-mgmt.milestones.number-generation');
        Route::get('/by-project/{projectId}', [MilestoneController::class, 'getProjectMilestones'])->name('project-mgmt.milestones.by-project');
    });

    // Resource Management Routes
    Route::prefix('project-resources')->group(function () {
        // Main CRUD operations
        Route::get('/load-all', [ResourceController::class, 'index'])->name('project-mgmt.resources.load-all');
        Route::post('/construct-new', [ResourceController::class, 'store'])->name('project-mgmt.resources.construct-new');
        Route::get('/display/{id}', [ResourceController::class, 'show'])->name('project-mgmt.resources.display');
        Route::put('/alter/{id}', [ResourceController::class, 'update'])->name('project-mgmt.resources.alter');
        Route::delete('/purge/{id}', [ResourceController::class, 'destroy'])->name('project-mgmt.resources.purge');

        // Advanced search and filtering
        Route::post('/advanced-search', [ResourceController::class, 'search'])->name('project-mgmt.resources.advanced-search');
        Route::get('/filter-by-field', [ResourceController::class, 'getResourcesByField'])->name('project-mgmt.resources.filter-by-field');
        Route::get('/field-values', [ResourceController::class, 'getFieldValues'])->name('project-mgmt.resources.field-values');
        Route::get('/sortable-fields', [ResourceController::class, 'getSortableFields'])->name('project-mgmt.resources.sortable-fields');
        Route::post('/apply-sort', [ResourceController::class, 'sortResources'])->name('project-mgmt.resources.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-resource', [ResourceController::class, 'restore'])->name('project-mgmt.resources.restore-resource');
        Route::delete('/{id}/permanent-delete', [ResourceController::class, 'forceDelete'])->name('project-mgmt.resources.permanent-delete');
        Route::get('/deleted-resources', [ResourceController::class, 'getTrashed'])->name('project-mgmt.resources.deleted-resources');

        // Helper endpoints for dropdown data
        Route::get('/supplier-options', [ResourceController::class, 'getSuppliers'])->name('project-mgmt.resources.supplier-options');
        Route::get('/project-options', [ResourceController::class, 'getProjects'])->name('project-mgmt.resources.project-options');
        Route::get('/type-options', [ResourceController::class, 'getResourceTypes'])->name('project-mgmt.resources.type-options');
        Route::get('/status-options', [ResourceController::class, 'getStatusOptions'])->name('project-mgmt.resources.status-options');

        // Allocation calculation endpoints
        Route::post('/allocation-calculation', [ResourceController::class, 'calculateAllocation'])->name('project-mgmt.resources.allocation-calculation');
        Route::post('/percentage-calculation', [ResourceController::class, 'calculateAllocationPercentage'])->name('project-mgmt.resources.percentage-calculation');

        // Specialized resource views
        Route::get('/by-project/{projectId}', [ResourceController::class, 'getProjectResources'])->name('project-mgmt.resources.by-project');
        Route::get('/by-supplier/{supplierId}', [ResourceController::class, 'getSupplierResources'])->name('project-mgmt.resources.by-supplier');
    });

    // Document Management Routes
    Route::prefix('project-documents')->group(function () {
        // Main CRUD operations
        Route::get('/gather-all', [DocumentController::class, 'index'])->name('project-mgmt.documents.gather-all');
        Route::post('/compose-new', [DocumentController::class, 'store'])->name('project-mgmt.documents.compose-new');
        Route::get('/read/{id}', [DocumentController::class, 'show'])->name('project-mgmt.documents.read');
        Route::put('/amend/{id}', [DocumentController::class, 'update'])->name('project-mgmt.documents.amend');
        Route::delete('/erase/{id}', [DocumentController::class, 'destroy'])->name('project-mgmt.documents.erase');

        // File operations
        Route::get('/{id}/file-download', [DocumentController::class, 'downloadDocument'])->name('project-mgmt.documents.file-download');

        // Advanced search and filtering
        Route::post('/advanced-search', [DocumentController::class, 'search'])->name('project-mgmt.documents.advanced-search');
        Route::get('/filter-by-field', [DocumentController::class, 'getDocumentsByField'])->name('project-mgmt.documents.filter-by-field');
        Route::get('/field-values', [DocumentController::class, 'getFieldValues'])->name('project-mgmt.documents.field-values');
        Route::get('/sortable-fields', [DocumentController::class, 'getSortableFields'])->name('project-mgmt.documents.sortable-fields');
        Route::post('/apply-sort', [DocumentController::class, 'sortDocuments'])->name('project-mgmt.documents.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-document', [DocumentController::class, 'restore'])->name('project-mgmt.documents.restore-document');
        Route::delete('/{id}/permanent-delete', [DocumentController::class, 'forceDelete'])->name('project-mgmt.documents.permanent-delete');
        Route::get('/deleted-documents', [DocumentController::class, 'getTrashed'])->name('project-mgmt.documents.deleted-documents');

        // Helper endpoints for dropdown data
        Route::get('/project-options', [DocumentController::class, 'getProjects'])->name('project-mgmt.documents.project-options');
        Route::get('/category-options', [DocumentController::class, 'getDocumentCategories'])->name('project-mgmt.documents.category-options');
        Route::get('/status-options', [DocumentController::class, 'getStatusOptions'])->name('project-mgmt.documents.status-options');

        // Utility endpoints
        Route::post('/number-generation', [DocumentController::class, 'generateDocumentNumber'])->name('project-mgmt.documents.number-generation');

        // Specialized document views
        Route::get('/by-project/{projectId}', [DocumentController::class, 'getProjectDocuments'])->name('project-mgmt.documents.by-project');
        Route::get('/by-category/{category}', [DocumentController::class, 'getDocumentsByCategory'])->name('project-mgmt.documents.by-category');
    });

    // Project Financial Management Routes
    Route::prefix('project-finance')->group(function () {
        // Main CRUD operations
        Route::get('/obtain-all', [ProjectFinancialController::class, 'index'])->name('project-mgmt.finance.obtain-all');
        Route::post('/register-new', [ProjectFinancialController::class, 'store'])->name('project-mgmt.finance.register-new');
        Route::get('/show/{id}', [ProjectFinancialController::class, 'show'])->name('project-mgmt.finance.show');
        Route::put('/adjust/{id}', [ProjectFinancialController::class, 'update'])->name('project-mgmt.finance.adjust');
        Route::delete('/cancel/{id}', [ProjectFinancialController::class, 'destroy'])->name('project-mgmt.finance.cancel');

        // Advanced search and filtering
        Route::post('/advanced-search', [ProjectFinancialController::class, 'search'])->name('project-mgmt.finance.advanced-search');
        Route::get('/filter-by-field', [ProjectFinancialController::class, 'getProjectFinancialsByField'])->name('project-mgmt.finance.filter-by-field');
        Route::get('/field-values', [ProjectFinancialController::class, 'getFieldValues'])->name('project-mgmt.finance.field-values');
        Route::get('/sortable-fields', [ProjectFinancialController::class, 'getSortableFields'])->name('project-mgmt.finance.sortable-fields');
        Route::post('/apply-sort', [ProjectFinancialController::class, 'sortProjectFinancials'])->name('project-mgmt.finance.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-financial', [ProjectFinancialController::class, 'restore'])->name('project-mgmt.finance.restore-financial');
        Route::delete('/{id}/permanent-delete', [ProjectFinancialController::class, 'forceDelete'])->name('project-mgmt.finance.permanent-delete');
        Route::get('/deleted-financials', [ProjectFinancialController::class, 'getTrashed'])->name('project-mgmt.finance.deleted-financials');

        // Helper endpoints for dropdown data
        Route::get('/project-options', [ProjectFinancialController::class, 'getProjects'])->name('project-mgmt.finance.project-options');
        Route::get('/currency-options', [ProjectFinancialController::class, 'getCurrencies'])->name('project-mgmt.finance.currency-options');

        // Specialized project financial views
        Route::get('/by-project/{projectId}', [ProjectFinancialController::class, 'getProjectFinancials'])->name('project-mgmt.finance.by-project');
        Route::get('/by-reference-type/{referenceType}', [ProjectFinancialController::class, 'getByReferenceType'])->name('project-mgmt.finance.by-reference-type');
        Route::get('/by-date-range/{dateFrom}/{dateTo}', [ProjectFinancialController::class, 'getByDateRange'])->name('project-mgmt.finance.by-date-range');
    });

    // Project Risk Management Routes
    Route::prefix('project-risk-management')->group(function () {
        // Statistics and analytics (must come before {id} routes)
        Route::get('/risk-statistics', [ProjectRiskController::class, 'getStatistics'])->name('project-mgmt.risks.risk-statistics');

        // Main CRUD operations
        Route::get('/collect-all', [ProjectRiskController::class, 'index'])->name('project-mgmt.risks.collect-all');
        Route::post('/formulate-new', [ProjectRiskController::class, 'store'])->name('project-mgmt.risks.formulate-new');
        Route::get('/present/{id}', [ProjectRiskController::class, 'show'])->name('project-mgmt.risks.present');
        Route::put('/modify-existing/{id}', [ProjectRiskController::class, 'update'])->name('project-mgmt.risks.modify-existing');
        Route::delete('/terminate/{id}', [ProjectRiskController::class, 'destroy'])->name('project-mgmt.risks.terminate');

        // Advanced search and filtering
        Route::post('/advanced-search', [ProjectRiskController::class, 'search'])->name('project-mgmt.risks.advanced-search');
        Route::get('/filter-by-field', [ProjectRiskController::class, 'getProjectRisksByField'])->name('project-mgmt.risks.filter-by-field');
        Route::get('/field-values', [ProjectRiskController::class, 'getFieldValues'])->name('project-mgmt.risks.field-values');
        Route::get('/sortable-fields', [ProjectRiskController::class, 'getSortableFields'])->name('project-mgmt.risks.sortable-fields');
        Route::post('/apply-sort', [ProjectRiskController::class, 'sortProjectRisks'])->name('project-mgmt.risks.apply-sort');

        // Soft delete management
        Route::post('/{id}/restore-risk', [ProjectRiskController::class, 'restore'])->name('project-mgmt.risks.restore-risk');
        Route::delete('/{id}/permanent-delete', [ProjectRiskController::class, 'forceDelete'])->name('project-mgmt.risks.permanent-delete');
        Route::get('/deleted-risks', [ProjectRiskController::class, 'getTrashed'])->name('project-mgmt.risks.deleted-risks');

        // Helper endpoints for dropdown data (bidirectional linking)
        Route::get('/project-options', [ProjectRiskController::class, 'getProjects'])->name('project-mgmt.risks.project-options');
        Route::get('/employee-options', [ProjectRiskController::class, 'getEmployees'])->name('project-mgmt.risks.employee-options');

        // Dropdown options for risk fields
        Route::get('/impact-options', [ProjectRiskController::class, 'getImpactOptions'])->name('project-mgmt.risks.impact-options');
        Route::get('/probability-options', [ProjectRiskController::class, 'getProbabilityOptions'])->name('project-mgmt.risks.probability-options');
        Route::get('/status-options', [ProjectRiskController::class, 'getStatusOptions'])->name('project-mgmt.risks.status-options');

        // Specialized project risk views
        Route::get('/by-project/{projectId}', [ProjectRiskController::class, 'getProjectRisks'])->name('project-mgmt.risks.by-project');
        Route::get('/by-status/{status}', [ProjectRiskController::class, 'getByStatus'])->name('project-mgmt.risks.by-status');
        Route::get('/by-impact/{impact}', [ProjectRiskController::class, 'getByImpact'])->name('project-mgmt.risks.by-impact');
        Route::get('/by-probability/{probability}', [ProjectRiskController::class, 'getByProbability'])->name('project-mgmt.risks.by-probability');
    });
});
