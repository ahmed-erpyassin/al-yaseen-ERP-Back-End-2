<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\app\Services\Employee\EmployeeService;
use Modules\HumanResources\Http\Requests\Employee\EmployeeRequest;
use Modules\HumanResources\Transformers\Employee\EmployeeResource;

class EmployeeController extends Controller
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the employees with advanced search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $employees = $this->service->list($request);

            return response()->json([
                'success' => true,
                'data' => EmployeeResource::collection($employees->items()),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                ],
                'filters' => [
                    'search' => $request->get('search'),
                    'employee_name' => $request->get('employee_name'),
                    'department_id' => $request->get('department_id'),
                    'currency_id' => $request->get('currency_id'),
                    'employee_number_from' => $request->get('employee_number_from'),
                    'employee_number_to' => $request->get('employee_number_to'),
                    'balance_from' => $request->get('balance_from'),
                    'balance_to' => $request->get('balance_to'),
                    'sort_by' => $request->get('sort_by', 'created_at'),
                    'sort_direction' => $request->get('sort_direction', 'desc'),
                ],
                'message' => 'Employees retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching employees.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(EmployeeRequest $request): JsonResponse
    {
        try {
            $employee = $this->service->create($request);
            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee),
                'message' => 'Employee created successfully.'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): JsonResponse
    {
        try {
            $employee = $this->service->show($employee);
            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee),
                'message' => 'Employee retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee): JsonResponse
    {
        try {
            $employee = $this->service->update($request, $employee);
            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee),
                'message' => 'Employee updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified employee from storage (soft delete).
     */
    public function destroy(Employee $employee): JsonResponse
    {
        try {
            $deleted = $this->service->delete($employee);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee deleted successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to delete employee.',
                    'message' => 'Employee could not be deleted.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted employee
     */
    public function restore(int $employeeId): JsonResponse
    {
        try {
            $employee = $this->service->restore($employeeId);

            return response()->json([
                'success' => true,
                'data' => new EmployeeResource($employee),
                'message' => 'Employee restored successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted employees (soft deleted)
     */
    public function deleted(Request $request): JsonResponse
    {
        try {
            $companyId = Auth::user()->company->id;

            $query = Employee::onlyTrashed()
                ->with(['department', 'jobTitle', 'branch', 'currency', 'manager'])
                ->forCompany($companyId);

            // Apply search to deleted employees
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('employee_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $employees = $query->orderBy('deleted_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => EmployeeResource::collection($employees->items()),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                ],
                'message' => 'Deleted employees retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted employees.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next employee number
     */
    public function getNextEmployeeNumber(): JsonResponse
    {
        try {
            $companyId = Auth::user()->company->id;
            $nextNumber = Employee::generateEmployeeNumber($companyId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'employee_number' => $nextNumber
                ],
                'message' => 'Next employee number generated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while generating employee number.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for employee creation/editing
     */
    public function getFormData(): JsonResponse
    {
        try {
            $formData = $this->service->getFormData();
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => 'Form data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for employees
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'search' => 'nullable|string|max:255',
                'employee_name' => 'nullable|string|max:255',
                'department_id' => 'nullable|integer|exists:departments,id',
                'currency_id' => 'nullable|integer|exists:currencies,id',
                'employee_number_from' => 'nullable|string|max:50',
                'employee_number_to' => 'nullable|string|max:50',
                'balance_from' => 'nullable|numeric',
                'balance_to' => 'nullable|numeric',
                'salary_from' => 'nullable|numeric',
                'salary_to' => 'nullable|numeric',
                'hire_date_from' => 'nullable|date',
                'hire_date_to' => 'nullable|date',
                'birth_date_from' => 'nullable|date',
                'birth_date_to' => 'nullable|date',
                'gender' => 'nullable|in:male,female',
                'is_driver' => 'nullable|boolean',
                'is_sales' => 'nullable|boolean',
                'sort_by' => 'nullable|string',
                'sort_direction' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            $employees = $this->service->list($request);

            return response()->json([
                'success' => true,
                'data' => EmployeeResource::collection($employees->items()),
                'pagination' => [
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'per_page' => $employees->perPage(),
                    'total' => $employees->total(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                ],
                'search_criteria' => $request->only([
                    'search', 'employee_name', 'department_id', 'currency_id',
                    'employee_number_from', 'employee_number_to', 'balance_from', 'balance_to',
                    'salary_from', 'salary_to', 'hire_date_from', 'hire_date_to',
                    'birth_date_from', 'birth_date_to', 'gender', 'is_driver', 'is_sales'
                ]),
                'message' => 'Employee search completed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching employees.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
