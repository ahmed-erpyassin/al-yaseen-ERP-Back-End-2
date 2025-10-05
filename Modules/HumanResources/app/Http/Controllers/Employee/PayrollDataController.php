<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HumanResources\Models\PayrollData;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\app\Services\Employee\PayrollDataService;
use Modules\HumanResources\Http\Requests\Employee\PayrollDataRequest;
use Modules\HumanResources\Transformers\Employee\PayrollDataResource;

/**
 * @group Employee/Payroll Data Management
 *
 * APIs for managing detailed payroll data entries including deductions, bonuses, overtime, and other salary components.
 */
class PayrollDataController extends Controller
{
    protected PayrollDataService $service;

    public function __construct(PayrollDataService $service)
    {
        $this->service = $service;
    }

    /**
     * Display payroll data for a specific payroll record
     */
    public function index(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $payrollData = $this->service->listForPayrollRecord($payrollRecord, $request);

            return response()->json([
                'success' => true,
                'data' => PayrollDataResource::collection($payrollData),
                'message' => 'Payroll data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payroll data entry
     */
    public function store(PayrollDataRequest $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payrollData = $this->service->create($request, $payrollRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new PayrollDataResource($payrollData),
                'message' => 'Payroll data created successfully.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to create payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payroll data
     */
    public function show(PayrollRecord $payrollRecord, PayrollData $payrollData): JsonResponse
    {
        try {
            // Ensure payroll data belongs to the payroll record
            if ($payrollData->payroll_record_id !== $payrollRecord->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payroll data does not belong to the specified payroll record.',
                    'message' => 'Invalid payroll data relationship.'
                ], 400);
            }

            $payrollData = $this->service->show($payrollData);

            return response()->json([
                'success' => true,
                'data' => new PayrollDataResource($payrollData),
                'message' => 'Payroll data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified payroll data
     */
    public function update(PayrollDataRequest $request, PayrollRecord $payrollRecord, PayrollData $payrollData): JsonResponse
    {
        try {
            // Ensure payroll data belongs to the payroll record
            if ($payrollData->payroll_record_id !== $payrollRecord->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payroll data does not belong to the specified payroll record.',
                    'message' => 'Invalid payroll data relationship.'
                ], 400);
            }

            DB::beginTransaction();

            $payrollData = $this->service->update($request, $payrollData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new PayrollDataResource($payrollData),
                'message' => 'Payroll data updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to update payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payroll data (soft delete)
     */
    public function destroy(Request $request, PayrollRecord $payrollRecord, PayrollData $payrollData): JsonResponse
    {
        try {
            // Ensure payroll data belongs to the payroll record
            if ($payrollData->payroll_record_id !== $payrollRecord->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payroll data does not belong to the specified payroll record.',
                    'message' => 'Invalid payroll data relationship.'
                ], 400);
            }

            // Check if payroll record belongs to the company
            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            // Check if payroll record can be modified (only draft status)
            if ($payrollRecord->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete payroll data.',
                    'message' => 'Only payroll data from draft records can be deleted.'
                ], 400);
            }

            DB::beginTransaction();

            // Soft delete the payroll data
            $this->service->softDelete($payrollData);

            // Recalculate payroll record totals
            $payrollRecord->calculateTotals();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll data deleted successfully.',
                'deleted_at' => $payrollData->fresh()->deleted_at
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to delete payroll data', [
                'payroll_data_id' => $payrollData->id,
                'payroll_record_id' => $payrollRecord->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Populate payroll data from employee information
     */
    public function populateFromEmployee(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $request->validate([
                'employee_id' => 'required|integer|exists:employees,id'
            ]);

            $employee = Employee::find($request->employee_id);
            $payrollData = $this->service->populateFromEmployee($employee, $payrollRecord);

            return response()->json([
                'success' => true,
                'data' => new PayrollDataResource($payrollData),
                'message' => 'Payroll data populated from employee successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to populate payroll data from employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate amounts for payroll data
     */
    public function recalculateAmounts(PayrollRecord $payrollRecord, PayrollData $payrollData): JsonResponse
    {
        try {
            // Ensure payroll data belongs to the payroll record
            if ($payrollData->payroll_record_id !== $payrollRecord->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payroll data does not belong to the specified payroll record.',
                    'message' => 'Invalid payroll data relationship.'
                ], 400);
            }

            $payrollData = $this->service->recalculateAmounts($payrollData);

            return response()->json([
                'success' => true,
                'data' => new PayrollDataResource($payrollData),
                'message' => 'Payroll data amounts recalculated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to recalculate payroll data amounts.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk add employees to payroll record
     */
    public function bulkAddEmployees(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $request->validate([
                'employee_ids' => 'required|array',
                'employee_ids.*' => 'integer|exists:employees,id'
            ]);

            DB::beginTransaction();

            $payrollDataEntries = $this->service->bulkAddEmployees($request->employee_ids, $payrollRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => PayrollDataResource::collection($payrollDataEntries),
                'message' => 'Employees added to payroll record successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to add employees to payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sorted payroll data
     */
    public function getSorted(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $request->validate([
                'sort_field' => 'required|string|in:employee_number,employee_name,national_id,marital_status,job_title,duration,basic_salary,income_tax,salary_for_payment,paid_in_cash,allowances,deductions,overtime_hours,overtime_rate,overtime_amount,status',
                'sort_direction' => 'required|string|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $sortedData = $this->service->getSortedPayrollData($payrollRecord, $request);

            return response()->json([
                'success' => true,
                'data' => $sortedData,
                'message' => 'Sorted payroll data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve sorted payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get first and last payroll data for navigation
     */
    public function getFirstLast(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $request->validate([
                'sort_field' => 'required|string|in:employee_number,employee_name,national_id,marital_status,job_title,duration,basic_salary,income_tax,salary_for_payment,paid_in_cash,allowances,deductions,overtime_hours,overtime_rate,overtime_amount,status',
                'sort_direction' => 'required|string|in:asc,desc'
            ]);

            $firstLastData = $this->service->getFirstLastPayrollData($payrollRecord, $request);

            return response()->json([
                'success' => true,
                'data' => $firstLastData,
                'message' => 'First and last payroll data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve first and last payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
