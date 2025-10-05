<?php

namespace Modules\HumanResources\app\Services\Employee;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\HumanResources\Http\Requests\Employee\EmployeeRequest;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Models\JobTitle;
use Modules\Companies\Models\Branch;
use Modules\FinancialAccounts\Models\Currency;
use Modules\FinancialAccounts\Models\FiscalYear;

class EmployeeService
{
    /**
     * Get paginated list of employees with filters and search
     */
    public function list(Request $request): LengthAwarePaginator
    {
        $companyId = Auth::user()->company->id;
        
        $query = Employee::with([
            'company',
            'branch',
            'department',
            'jobTitle',
            'manager',
            'currency',
            'creator',
            'updater'
        ])->forCompany($companyId);

        // Apply filters
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('job_title_id')) {
            $query->where('job_title_id', $request->job_title_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->filled('is_driver')) {
            $query->where('is_driver', $request->boolean('is_driver'));
        }

        if ($request->filled('is_sales')) {
            $query->where('is_sales', $request->boolean('is_sales'));
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Employee Number Range Search
        if ($request->filled('employee_number_from')) {
            $query->where('employee_number', '>=', $request->employee_number_from);
        }

        if ($request->filled('employee_number_to')) {
            $query->where('employee_number', '<=', $request->employee_number_to);
        }

        // Balance Range Search
        if ($request->filled('balance_from')) {
            $query->where('balance', '>=', $request->balance_from);
        }

        if ($request->filled('balance_to')) {
            $query->where('balance', '<=', $request->balance_to);
        }

        // Salary Range Search
        if ($request->filled('salary_from')) {
            $query->where('salary', '>=', $request->salary_from);
        }

        if ($request->filled('salary_to')) {
            $query->where('salary', '<=', $request->salary_to);
        }

        // Date Range Filters
        if ($request->filled('hire_date_from')) {
            $query->whereDate('hire_date', '>=', $request->hire_date_from);
        }

        if ($request->filled('hire_date_to')) {
            $query->whereDate('hire_date', '<=', $request->hire_date_to);
        }

        if ($request->filled('birth_date_from')) {
            $query->whereDate('birth_date', '>=', $request->birth_date_from);
        }

        if ($request->filled('birth_date_to')) {
            $query->whereDate('birth_date', '<=', $request->birth_date_to);
        }

        // Apply search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('second_name', 'like', "%{$search}%")
                  ->orWhere('third_name', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone1', 'like', "%{$search}%")
                  ->orWhere('phone2', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('id_number', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('employee_identifier', 'like', "%{$search}%")
                  ->orWhere('car_number', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('job_address', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Employee Name Search (specific field)
        if ($request->filled('employee_name')) {
            $name = $request->employee_name;
            $query->where(function ($q) use ($name) {
                $q->where('first_name', 'like', "%{$name}%")
                  ->orWhere('last_name', 'like', "%{$name}%")
                  ->orWhere('second_name', 'like', "%{$name}%")
                  ->orWhere('third_name', 'like', "%{$name}%")
                  ->orWhere('nickname', 'like', "%{$name}%");
            });
        }

        // Apply sorting with validation
        $allowedSortFields = [
            'id', 'employee_number', 'first_name', 'last_name', 'second_name', 'third_name',
            'email', 'phone1', 'birth_date', 'hire_date', 'salary', 'balance', 'gender',
            'is_driver', 'is_sales', 'created_at', 'updated_at', 'department_id', 'job_title_id',
            'branch_id', 'currency_id', 'national_id', 'address'
        ];

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort field
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Add secondary sorting for consistent results
        if ($sortBy !== 'id') {
            $query->orderBy('id', 'desc');
        }

        $perPage = $request->get('per_page', 15);
        return $query->paginate($perPage);
    }

    /**
     * Create a new employee
     */
    public function create(EmployeeRequest $request): Employee
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            // Get live currency rate if not provided
            if (!isset($data['currency_rate']) && isset($data['currency_id'])) {
                $data['currency_rate'] = $this->getLiveCurrencyRate($data['currency_id']);
            }
            
            // Set audit fields
            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();
            
            return Employee::create($data);
        });
    }

    /**
     * Show employee with relationships
     */
    public function show(Employee $employee): Employee
    {
        return $employee->load([
            'company',
            'user',
            'branch',
            'fiscalYear',
            'department',
            'jobTitle',
            'manager',
            'subordinates',
            'currency',
            'creator',
            'updater',
            'deleter'
        ]);
    }

    /**
     * Update employee with comprehensive validation and logging
     */
    public function update(EmployeeRequest $request, Employee $employee): Employee
    {
        return DB::transaction(function () use ($request, $employee) {
            $data = $request->validated();
            $originalData = $employee->toArray();

            // Get live currency rate if currency changed
            if (isset($data['currency_id']) && $data['currency_id'] != $employee->currency_id) {
                $data['currency_rate'] = $this->getLiveCurrencyRate($data['currency_id']);
            }

            // Handle employee number change
            if (isset($data['employee_number']) && $data['employee_number'] !== $employee->employee_number) {
                // Validate that the new employee number is not taken
                $existingEmployee = Employee::where('employee_number', $data['employee_number'])
                    ->where('id', '!=', $employee->id)
                    ->where('company_id', $employee->company_id)
                    ->first();

                if ($existingEmployee) {
                    throw new \Exception('Employee number already exists.');
                }
            }

            // Handle email change
            if (isset($data['email']) && $data['email'] !== $employee->email) {
                // Validate that the new email is not taken
                $existingEmployee = Employee::where('email', $data['email'])
                    ->where('id', '!=', $employee->id)
                    ->first();

                if ($existingEmployee) {
                    throw new \Exception('Email address already exists.');
                }
            }

            // Set audit fields
            $data['updated_by'] = Auth::id();

            // Update the employee
            $employee->update($data);

            // Log the changes (you can implement detailed logging here)
            $this->logEmployeeChanges($employee, $originalData, $data);

            return $employee->fresh()->load([
                'company', 'user', 'branch', 'fiscalYear', 'department',
                'jobTitle', 'manager', 'currency', 'creator', 'updater'
            ]);
        });
    }

    /**
     * Delete employee (soft delete) with validation
     */
    public function delete(Employee $employee): bool
    {
        return DB::transaction(function () use ($employee) {
            // Check if employee has subordinates
            $subordinatesCount = $employee->subordinates()->count();
            if ($subordinatesCount > 0) {
                throw new \Exception("Cannot delete employee. This employee manages {$subordinatesCount} subordinate(s). Please reassign them first.");
            }

            // Check if employee has related records (you can add more checks here)
            // For example: attendance records, leave requests, etc.

            // Set deleted_by before soft delete
            $employee->update(['deleted_by' => Auth::id()]);

            // Perform soft delete
            $deleted = $employee->delete();

            // Log the deletion
            Log::info("Employee deleted", [
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'employee_name' => $employee->full_name,
                'deleted_by' => Auth::id(),
                'deleted_at' => now()
            ]);

            return $deleted;
        });
    }

    /**
     * Restore soft deleted employee
     */
    public function restore(int $employeeId): Employee
    {
        return DB::transaction(function () use ($employeeId) {
            $employee = Employee::withTrashed()->findOrFail($employeeId);

            // Check if employee belongs to current user's company
            if ($employee->company_id !== Auth::user()->company->id) {
                throw new \Exception('Employee not found or access denied.');
            }

            $employee->restore();
            $employee->update(['deleted_by' => null]);

            // Log the restoration
            Log::info("Employee restored", [
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'employee_name' => $employee->full_name,
                'restored_by' => Auth::id(),
                'restored_at' => now()
            ]);

            return $employee->fresh();
        });
    }

    /**
     * Get form data for employee creation/editing
     */
    public function getFormData(): array
    {
        $companyId = Auth::user()->company->id;
        
        return [
            'next_employee_number' => Employee::generateEmployeeNumber($companyId),
            'departments' => Department::where('company_id', $companyId)->get(['id', 'name']),
            'job_titles' => JobTitle::where('company_id', $companyId)->get(['id', 'title']),
            'branches' => Branch::where('company_id', $companyId)->get(['id', 'name']),
            'currencies' => Currency::where('is_active', true)->get(['id', 'code', 'name', 'symbol']),
            'fiscal_years' => FiscalYear::where('company_id', $companyId)->get(['id', 'name', 'start_date', 'end_date']),
            'managers' => Employee::where('company_id', $companyId)
                ->whereNull('deleted_at')
                ->get(['id', 'employee_number', 'first_name', 'last_name']),
            'gender_options' => [
                ['value' => 'male', 'label' => 'Male'],
                ['value' => 'female', 'label' => 'Female']
            ],
            'employee_types' => [
                ['value' => 'employee', 'label' => 'Employee'],
                ['value' => 'driver', 'label' => 'Driver'],
                ['value' => 'sales', 'label' => 'Sales Representative'],
                ['value' => 'driver_sales', 'label' => 'Driver & Sales Representative']
            ]
        ];
    }

    /**
     * Get live currency rate from external API or database
     */
    private function getLiveCurrencyRate(int $currencyId): float
    {
        try {
            $currency = Currency::find($currencyId);
            if (!$currency) {
                return 1.0;
            }

            // If it's the base currency, return 1.0
            if ($currency->is_base_currency) {
                return 1.0;
            }

            // Try to get live rate from external API
            // For now, return the stored rate or 1.0
            return $currency->exchange_rate ?? 1.0;
            
        } catch (\Exception $e) {
            return 1.0;
        }
    }

    /**
     * Log employee changes for audit trail
     */
    private function logEmployeeChanges(Employee $employee, array $originalData, array $newData): void
    {
        $changes = [];

        // Compare important fields
        $fieldsToTrack = [
            'employee_number', 'first_name', 'last_name', 'email', 'phone1', 'phone2',
            'department_id', 'job_title_id', 'branch_id', 'salary', 'balance', 'currency_id',
            'is_driver', 'is_sales', 'hire_date', 'birth_date', 'gender'
        ];

        foreach ($fieldsToTrack as $field) {
            if (isset($newData[$field]) && $originalData[$field] != $newData[$field]) {
                $changes[$field] = [
                    'old' => $originalData[$field],
                    'new' => $newData[$field]
                ];
            }
        }

        if (!empty($changes)) {
            Log::info("Employee updated", [
                'employee_id' => $employee->id,
                'employee_number' => $employee->employee_number,
                'employee_name' => $employee->full_name,
                'changes' => $changes,
                'updated_by' => Auth::id(),
                'updated_at' => now()
            ]);
        }
    }
}
