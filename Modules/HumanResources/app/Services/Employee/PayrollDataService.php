<?php

namespace Modules\HumanResources\app\Services\Employee;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HumanResources\Models\PayrollData;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\Http\Requests\Employee\PayrollDataRequest;
use Carbon\Carbon;

class PayrollDataService
{
    /**
     * Get paginated list of payroll data for a payroll record
     */
    public function listForPayrollRecord(PayrollRecord $payrollRecord, Request $request)
    {
        $query = PayrollData::with([
            'employee.jobTitle',
            'employee.department',
            'creator'
        ])->forPayrollRecord($payrollRecord->id);

        // Apply filters
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_name')) {
            $query->where('employee_name', 'like', '%' . $request->employee_name . '%');
        }

        if ($request->filled('employee_number')) {
            $query->where('employee_number', 'like', '%' . $request->employee_number . '%');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'employee_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Create new payroll data entry
     */
    public function create(PayrollDataRequest $request, PayrollRecord $payrollRecord): PayrollData
    {
        $data = $request->validated();

        // Set payroll record and company context
        $data['payroll_record_id'] = $payrollRecord->id;
        $data['company_id'] = $payrollRecord->company_id;
        $data['user_id'] = $payrollRecord->user_id;
        $data['branch_id'] = $payrollRecord->branch_id;
        $data['fiscal_year_id'] = $payrollRecord->fiscal_year_id;
        $data['created_by'] = Auth::id();

        // If employee_id is provided, populate data from employee
        if (isset($data['employee_id'])) {
            $employee = Employee::find($data['employee_id']);
            if ($employee) {
                $data = $this->populateDataFromEmployee($data, $employee);
            }
        }

        return PayrollData::create($data);
    }

    /**
     * Show payroll data with relationships
     */
    public function show(PayrollData $payrollData): PayrollData
    {
        return $payrollData->load([
            'payrollRecord',
            'employee.jobTitle',
            'employee.department',
            'creator',
            'updater'
        ]);
    }

    /**
     * Update payroll data
     */
    public function update(PayrollDataRequest $request, PayrollData $payrollData): PayrollData
    {
        return DB::transaction(function () use ($request, $payrollData) {
            $data = $request->validated();
            $data['updated_by'] = Auth::id();

            // If employee_id changed, populate new data from employee
            if (isset($data['employee_id']) && $data['employee_id'] != $payrollData->employee_id) {
                $employee = Employee::find($data['employee_id']);
                if ($employee) {
                    $data = $this->populateDataFromEmployee($data, $employee);
                }
            }

            $payrollData->update($data);

            // Recalculate amounts
            $payrollData->recalculateAmounts();
            $payrollData->save();

            return $payrollData->fresh();
        });
    }

    /**
     * Delete payroll data
     */
    public function delete(PayrollData $payrollData): bool
    {
        return DB::transaction(function () use ($payrollData) {
            $payrollData->update(['deleted_by' => Auth::id()]);
            return $payrollData->delete();
        });
    }

    /**
     * Populate payroll data from employee information
     */
    public function populateFromEmployee(Employee $employee, PayrollRecord $payrollRecord): PayrollData
    {
        // Check if employee already exists in this payroll record
        $existingData = PayrollData::where('payroll_record_id', $payrollRecord->id)
            ->where('employee_id', $employee->id)
            ->first();

        if ($existingData) {
            // Update existing data
            $existingData->populateFromEmployee($employee);
            $existingData->recalculateAmounts();
            $existingData->save();
            return $existingData;
        }

        // Create new payroll data
        $data = [
            'payroll_record_id' => $payrollRecord->id,
            'company_id' => $payrollRecord->company_id,
            'user_id' => $payrollRecord->user_id,
            'branch_id' => $payrollRecord->branch_id,
            'fiscal_year_id' => $payrollRecord->fiscal_year_id,
            'employee_id' => $employee->id,
            'created_by' => Auth::id(),
        ];

        $data = $this->populateDataFromEmployee($data, $employee);

        return PayrollData::create($data);
    }

    /**
     * Recalculate amounts for payroll data
     */
    public function recalculateAmounts(PayrollData $payrollData): PayrollData
    {
        $payrollData->recalculateAmounts();
        $payrollData->save();
        return $payrollData->fresh();
    }

    /**
     * Bulk add employees to payroll record
     */
    public function bulkAddEmployees(array $employeeIds, PayrollRecord $payrollRecord): array
    {
        $payrollDataEntries = [];

        foreach ($employeeIds as $employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                // Check if employee already exists in this payroll record
                $existingData = PayrollData::where('payroll_record_id', $payrollRecord->id)
                    ->where('employee_id', $employee->id)
                    ->first();

                if (!$existingData) {
                    $payrollDataEntries[] = $this->populateFromEmployee($employee, $payrollRecord);
                }
            }
        }

        return $payrollDataEntries;
    }

    /**
     * Get employees not in payroll record
     */
    public function getAvailableEmployees(PayrollRecord $payrollRecord, Request $request)
    {
        $existingEmployeeIds = PayrollData::where('payroll_record_id', $payrollRecord->id)
            ->pluck('employee_id')
            ->toArray();

        $query = Employee::forCompany($payrollRecord->company_id)
            ->whereNotIn('id', $existingEmployeeIds)
            ->with(['jobTitle', 'department']);

        // Apply search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(second_name, ''), ' ', COALESCE(third_name, ''), ' ', last_name) LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('employee_number')->get();
    }

    /**
     * Calculate totals for payroll record based on current page data
     */
    public function calculatePageTotals($payrollDataCollection): array
    {
        return [
            'total_basic_salaries' => $payrollDataCollection->sum('basic_salary'),
            'total_income_tax_deductions' => $payrollDataCollection->sum('income_tax'),
            'total_payable_amount' => $payrollDataCollection->sum('salary_for_payment'),
            'total_paid_cash' => $payrollDataCollection->sum('paid_in_cash'),
            'total_allowances' => $payrollDataCollection->sum('allowances'),
            'total_deductions' => $payrollDataCollection->sum('deductions'),
            'total_overtime_amount' => $payrollDataCollection->sum('overtime_amount'),
        ];
    }

    /**
     * Private helper to populate data from employee
     */
    private function populateDataFromEmployee(array $data, Employee $employee): array
    {
        $data['employee_number'] = $employee->employee_number;
        $data['employee_name'] = $employee->full_name;
        $data['national_id'] = $employee->national_id;
        $data['marital_status'] = $employee->marital_status;
        $data['job_title'] = $employee->jobTitle ? $employee->jobTitle->name : null;
        $data['basic_salary'] = $employee->salary ?? 0;

        // Calculate duration (years of service)
        if ($employee->hire_date) {
            $years = $employee->hire_date->diffInYears(now());
            $months = $employee->hire_date->diffInMonths(now()) % 12;
            $data['duration'] = "{$years} years, {$months} months";
        }

        // Set default income tax (can be calculated based on salary or set manually)
        if (!isset($data['income_tax'])) {
            $data['income_tax'] = $this->calculateDefaultIncomeTax($data['basic_salary']);
        }

        return $data;
    }

    /**
     * Calculate default income tax based on basic salary
     */
    private function calculateDefaultIncomeTax($basicSalary): float
    {
        // Simple tax calculation - can be customized based on business rules
        if ($basicSalary <= 1000) {
            return 0;
        } elseif ($basicSalary <= 3000) {
            return $basicSalary * 0.05; // 5%
        } elseif ($basicSalary <= 5000) {
            return $basicSalary * 0.10; // 10%
        } else {
            return $basicSalary * 0.15; // 15%
        }
    }

    /**
     * Get sorted payroll data
     */
    public function getSortedPayrollData(PayrollRecord $payrollRecord, Request $request): array
    {
        $query = $payrollRecord->payrollData()
            ->with(['employee.jobTitle', 'employee.department']);

        // Apply sorting
        $query->orderBy($request->sort_field, $request->sort_direction);

        // Apply pagination
        $perPage = $request->get('per_page', 15);
        $payrollData = $query->paginate($perPage);

        // Calculate totals for current page
        $currentPageData = $payrollData->items();
        $pageTotals = [
            'total_basic_salary' => collect($currentPageData)->sum('basic_salary'),
            'total_income_tax' => collect($currentPageData)->sum('income_tax'),
            'total_salary_for_payment' => collect($currentPageData)->sum('salary_for_payment'),
            'total_paid_in_cash' => collect($currentPageData)->sum('paid_in_cash'),
            'total_allowances' => collect($currentPageData)->sum('allowances'),
            'total_deductions' => collect($currentPageData)->sum('deductions'),
            'total_overtime_amount' => collect($currentPageData)->sum('overtime_amount'),
        ];

        return [
            'payroll_data' => $payrollData,
            'page_totals' => $pageTotals,
            'sorting' => [
                'field' => $request->sort_field,
                'direction' => $request->sort_direction
            ]
        ];
    }

    /**
     * Get first and last payroll data for navigation
     */
    public function getFirstLastPayrollData(PayrollRecord $payrollRecord, Request $request): array
    {
        $query = $payrollRecord->payrollData()
            ->with(['employee.jobTitle', 'employee.department']);

        // Get first record
        $firstRecord = (clone $query)->orderBy($request->sort_field, 'asc')->first();

        // Get last record
        $lastRecord = (clone $query)->orderBy($request->sort_field, 'desc')->first();

        return [
            'first_record' => $firstRecord,
            'last_record' => $lastRecord,
            'sorting' => [
                'field' => $request->sort_field,
                'direction' => $request->sort_direction
            ]
        ];
    }

    /**
     * Soft delete payroll data
     */
    public function softDelete(PayrollData $payrollData): bool
    {
        // Set deleted_by before soft deleting
        $payrollData->deleted_by = Auth::id();
        $payrollData->save();

        // Soft delete the payroll data
        $payrollData->delete();

        return true;
    }

    /**
     * Force delete payroll data
     */
    public function forceDelete(PayrollData $payrollData): bool
    {
        // Force delete the payroll data
        $payrollData->forceDelete();

        return true;
    }

    /**
     * Restore payroll data
     */
    public function restore(PayrollData $payrollData): bool
    {
        // Restore the payroll data
        $payrollData->restore();

        return true;
    }
}
