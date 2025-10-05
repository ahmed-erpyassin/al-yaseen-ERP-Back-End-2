<?php

namespace Modules\HumanResources\app\Services;

use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\Http\Requests\DepartmentRequest;
use Modules\HumanResources\Models\Department;

class DepartmentService
{


    public function list($request = null)
    {
        $query = Department::with([
            'company', 'user', 'branch', 'fiscalYear', 'manager',
            'parent', 'budget', 'funder', 'creator', 'updater', 'deleter'
        ]);

        // Apply company filter
        if ($companyId = request()->company_id) {
            $query->forCompany($companyId);
        }

        // Apply search filters if provided
        if ($request) {
            $this->applySearchFilters($query, $request);
        }

        // Apply sorting if provided
        if ($request && $request->has('sort_by')) {
            $this->applySorting($query, $request);
        } else {
            // Default sorting by number
            $query->orderBy('number', 'asc');
        }

        return $query->paginate($request->get('per_page', 10));
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, $request)
    {
        // Search by department name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Search by department number range
        if ($request->filled('number_from')) {
            $query->where('number', '>=', $request->number_from);
        }
        if ($request->filled('number_to')) {
            $query->where('number', '<=', $request->number_to);
        }

        // Search by exact date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Search by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by proposed start date range
        if ($request->filled('proposed_start_date_from')) {
            $query->whereDate('proposed_start_date', '>=', $request->proposed_start_date_from);
        }
        if ($request->filled('proposed_start_date_to')) {
            $query->whereDate('proposed_start_date', '<=', $request->proposed_start_date_to);
        }

        // Search by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by project status
        if ($request->filled('project_status')) {
            $query->where('project_status', $request->project_status);
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'number');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validate sort direction
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        // Validate sort field
        $allowedSortFields = [
            'id', 'number', 'name', 'created_at', 'updated_at',
            'proposed_start_date', 'proposed_end_date', 'actual_start_date',
            'actual_end_date', 'status', 'project_status'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('number', 'asc');
        }
    }

    public function create(DepartmentRequest $request): Department
    {
        $data = $request->validated();

        // Auto-generate department number if not provided
        if (empty($data['number'])) {
            $data['number'] = Department::generateDepartmentNumber($data['company_id']);
        }

        // Auto-populate dates if not provided
        if (empty($data['proposed_start_date'])) {
            $data['proposed_start_date'] = now()->toDateString();
        }

        return Department::create($data + [
            'created_by' => Auth::id(),
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): Department
    {
        $data = $request->validated();

        // Handle department number - if empty, keep existing or generate new
        if (empty($data['number'])) {
            if (empty($department->number)) {
                $data['number'] = Department::generateDepartmentNumber($data['company_id']);
            } else {
                // Keep existing number
                unset($data['number']);
            }
        }

        // Update the department with validated data
        $department->update($data + [
            'updated_by' => Auth::id(),
        ]);

        // Reload relationships for response
        $department->load([
            'company', 'user', 'branch', 'fiscalYear', 'manager',
            'parent', 'budget', 'funder', 'creator', 'updater', 'deleter'
        ]);

        return $department;
    }

    /**
     * Get a single department with all relationships
     */
    public function show(Department $department): Department
    {
        $department->load([
            'company', 'user', 'branch', 'fiscalYear', 'manager',
            'parent', 'budget', 'funder', 'creator', 'updater', 'deleter'
        ]);

        return $department;
    }

    /**
     * Get the first department (for initial display)
     */
    public function first()
    {
        $companyId = request()->company_id;

        $department = Department::with([
            'company', 'user', 'branch', 'fiscalYear', 'manager',
            'parent', 'budget', 'funder', 'creator', 'updater', 'deleter'
        ])
        ->when($companyId, function($query) use ($companyId) {
            return $query->forCompany($companyId);
        })
        ->orderBy('number', 'asc')
        ->first();

        return $department;
    }

    public function delete(Department $department): void
    {
        $department->update(['deleted_by' => Auth::id()]);
        $department->delete();
    }
}
