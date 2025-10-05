<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HumanResources\Models\PayrollRecord;
use Modules\HumanResources\app\Services\Employee\PayrollService;
use Modules\HumanResources\Http\Requests\Employee\PayrollRecordRequest;
use Modules\HumanResources\Transformers\Employee\PayrollRecordResource;

/**
 * @group Employee/Payroll Management
 *
 * APIs for managing employee payroll records, salary calculations, deductions, bonuses, and payroll processing.
 */
class PayrollController extends Controller
{
    protected PayrollService $service;

    public function __construct(PayrollService $service)
    {
        $this->service = $service;
    }

    /**
     * List Payroll Records
     *
     * Retrieve a paginated list of payroll records with filtering options for employees, departments, and date ranges.
     *
     * @queryParam employee_id integer Filter by employee ID. Example: 1
     * @queryParam department_id integer Filter by department ID. Example: 1
     * @queryParam pay_period_start string Filter by pay period start date (YYYY-MM-DD). Example: 2025-01-01
     * @queryParam pay_period_end string Filter by pay period end date (YYYY-MM-DD). Example: 2025-01-31
     * @queryParam status string Filter by payroll status (draft, approved, paid). Example: approved
     * @queryParam sort_by string Sort by field (pay_period_start, gross_salary, net_salary). Example: pay_period_start
     * @queryParam sort_direction string Sort direction (asc, desc). Example: desc
     * @queryParam per_page integer Number of items per page (default: 15). Example: 20
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "employee_id": 1,
     *       "pay_period_start": "2025-01-01",
     *       "pay_period_end": "2025-01-31",
     *       "gross_salary": 5000.00,
     *       "deductions": 500.00,
     *       "bonuses": 200.00,
     *       "net_salary": 4700.00,
     *       "status": "approved",
     *       "employee": {
     *         "id": 1,
     *         "name": "John Doe",
     *         "employee_number": "EMP001",
     *         "department": {
     *           "id": 1,
     *           "name": "IT Department"
     *         }
     *       },
     *       "created_at": "2025-10-05T10:00:00.000000Z"
     *     }
     *   ],
     *   "message": "Payroll records retrieved successfully."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "Failed to retrieve payroll records.",
     *   "message": "Database connection failed"
     * }
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payrollRecords = $this->service->list($request);

            return response()->json([
                'success' => true,
                'data' => PayrollRecordResource::collection($payrollRecords),
                'message' => 'Payroll records retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll records.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payroll record
     */
    public function store(PayrollRecordRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $payrollRecord = $this->service->create($request);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord),
                'message' => 'Payroll record created successfully.'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Failed to create payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payroll record
     */
    public function show(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $payrollRecord = $this->service->show($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord),
                'message' => 'Payroll record retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified payroll record
     */
    public function update(PayrollRecordRequest $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            // Check if payroll record belongs to the company
            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            // Check if payroll record can be updated (only draft status)
            if ($payrollRecord->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot update payroll record.',
                    'message' => 'Only draft payroll records can be updated.'
                ], 400);
            }

            DB::beginTransaction();

            // Store original data for logging
            $originalData = $payrollRecord->toArray();

            // Update the payroll record
            $updatedPayrollRecord = $this->service->update($request, $payrollRecord);

            // Log the changes
            $changes = array_diff_assoc($request->validated(), $originalData);
            if (!empty($changes)) {
                \Log::info('Payroll record updated', [
                    'payroll_record_id' => $updatedPayrollRecord->id,
                    'user_id' => Auth::id(),
                    'changes' => $changes,
                    'original_data' => $originalData
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($updatedPayrollRecord->load([
                    'company', 'currency', 'account', 'payrollData.employee', 'creator', 'updater'
                ])),
                'message' => 'Payroll record updated successfully.',
                'changes_made' => !empty($changes),
                'updated_fields' => array_keys($changes)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'error' => 'Validation failed.',
                'message' => 'The provided data is invalid.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to update payroll record', [
                'payroll_record_id' => $payrollRecord->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payroll record (soft delete)
     */
    public function destroy(Request $request, PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            // Check if payroll record belongs to the company
            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            // Check if payroll record can be deleted (only draft status)
            if ($payrollRecord->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'error' => 'Cannot delete payroll record.',
                    'message' => 'Only draft payroll records can be deleted.'
                ], 400);
            }

            DB::beginTransaction();

            // Soft delete the payroll record and its data
            $this->service->softDelete($payrollRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll record deleted successfully.',
                'deleted_at' => $payrollRecord->fresh()->deleted_at
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to delete payroll record', [
                'payroll_record_id' => $payrollRecord->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete the specified payroll record
     */
    public function forceDelete(Request $request, $payrollRecordId): JsonResponse
    {
        try {
            $payrollRecord = PayrollRecord::withTrashed()->findOrFail($payrollRecordId);

            // Check if payroll record belongs to the company
            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            DB::beginTransaction();

            // Force delete the payroll record and its data
            $this->service->forceDelete($payrollRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll record permanently deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to permanently delete payroll record', [
                'payroll_record_id' => $payrollRecordId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to permanently delete payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted payroll record
     */
    public function restore(Request $request, $payrollRecordId): JsonResponse
    {
        try {
            $payrollRecord = PayrollRecord::withTrashed()->findOrFail($payrollRecordId);

            // Check if payroll record belongs to the company
            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            DB::beginTransaction();

            // Restore the payroll record and its data
            $this->service->restore($payrollRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord->fresh()),
                'message' => 'Payroll record restored successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to restore payroll record', [
                'payroll_record_id' => $payrollRecordId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to restore payroll record.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted payroll records
     */
    public function deleted(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id'
            ]);

            $deletedRecords = $this->service->getDeleted($request);

            return response()->json([
                'success' => true,
                'data' => $deletedRecords,
                'message' => 'Deleted payroll records retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve deleted payroll records.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payroll record with calculated totals
     */
    public function getWithTotals(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $payrollRecord = $this->service->getWithTotals($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord),
                'message' => 'Payroll record with totals retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll record with totals.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate totals for payroll record
     */
    public function recalculateTotals(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $payrollRecord = $this->service->recalculateTotals($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord),
                'message' => 'Payroll record totals recalculated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to recalculate payroll record totals.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate new payroll number
     */
    public function generatePayrollNumber(Request $request): JsonResponse
    {
        try {
            $payrollNumber = $this->service->generatePayrollNumber($request->company_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'payroll_number' => $payrollNumber
                ],
                'message' => 'Payroll number generated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate payroll number.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payroll statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        try {
            $statistics = $this->service->getStatistics($request);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Payroll statistics retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll statistics.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview payroll record with all data
     */
    public function preview(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $payrollRecord = $this->service->getFullPreview($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => new PayrollRecordResource($payrollRecord),
                'message' => 'Payroll record preview retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll record preview.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review payroll record with detailed analysis
     */
    public function review(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $reviewData = $this->service->getDetailedReview($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => $reviewData,
                'message' => 'Payroll record review data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve payroll record review.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all payroll data for a record with complete information
     */
    public function getAllData(PayrollRecord $payrollRecord): JsonResponse
    {
        try {
            $allData = $this->service->getAllPayrollData($payrollRecord);

            return response()->json([
                'success' => true,
                'data' => $allData,
                'message' => 'All payroll data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve all payroll data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sorted payroll records
     */
    public function getSorted(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'sort_field' => 'required|string|in:payroll_number,date,second_date,currency_rate,total_salaries,total_income_tax_deductions,total_payable_amount,total_salaries_paid_cash,status,created_at',
                'sort_direction' => 'required|string|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $sortedData = $this->service->getSortedPayrollRecords($request);

            return response()->json([
                'success' => true,
                'data' => $sortedData,
                'message' => 'Sorted payroll records retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve sorted payroll records.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get first and last payroll records for navigation
     */
    public function getFirstLast(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'sort_field' => 'required|string|in:payroll_number,date,second_date,currency_rate,total_salaries,total_income_tax_deductions,total_payable_amount,total_salaries_paid_cash,status,created_at',
                'sort_direction' => 'required|string|in:asc,desc'
            ]);

            $firstLastData = $this->service->getFirstLastPayrollRecords($request);

            return response()->json([
                'success' => true,
                'data' => $firstLastData,
                'message' => 'First and last payroll records retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to retrieve first and last payroll records.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
