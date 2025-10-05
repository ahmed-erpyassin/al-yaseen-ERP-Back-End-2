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

/**
 * @group Employee/Employee Management
 *
 * APIs for managing employees within the Human Resources module, including creation, updates, search, filtering, and employee relationship management.
 */
class EmployeeController extends Controller
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    /**
     * List Employees
     *
     * Retrieve a paginated list of employees with comprehensive filtering and search options.
     *
     * @queryParam search string Search across employee names and details. Example: John Doe
     * @queryParam employee_name string Search by employee name. Example: John
     * @queryParam department_id integer Filter by department ID. Example: 1
     * @queryParam currency_id integer Filter by currency ID. Example: 1
     * @queryParam employee_number_from integer Filter by employee number range (from). Example: 1
     * @queryParam employee_number_to integer Filter by employee number range (to). Example: 100
     * @queryParam balance_from decimal Filter by balance range (from). Example: 1000.00
     * @queryParam balance_to decimal Filter by balance range (to). Example: 5000.00
     * @queryParam hire_date_from string Filter by hire date range (from) (YYYY-MM-DD). Example: 2025-01-01
     * @queryParam hire_date_to string Filter by hire date range (to) (YYYY-MM-DD). Example: 2025-12-31
     * @queryParam status string Filter by employee status. Example: active
     * @queryParam sort_by string Sort by field (employee_number, name, hire_date, etc.). Example: name
     * @queryParam sort_direction string Sort direction (asc, desc). Example: asc
     * @queryParam per_page integer Number of items per page (default: 15). Example: 20
     * @queryParam company_id integer Filter by company ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "employee_number": "EMP001",
     *       "name": "John Doe",
     *       "email": "john.doe@example.com",
     *       "phone": "+1234567890",
     *       "hire_date": "2025-01-15",
     *       "department": {
     *         "id": 1,
     *         "name": "IT Department"
     *       },
     *       "job_title": {
     *         "id": 1,
     *         "name": "Software Developer"
     *       },
     *       "salary": 5000.00,
     *       "currency": {
     *         "id": 1,
     *         "name": "USD",
     *         "symbol": "$"
     *       },
     *       "status": "active",
     *       "balance": 2500.00,
     *       "created_at": "2025-10-05T10:00:00.000000Z"
     *     }
     *   ],
     *   "pagination": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 15,
     *     "total": 75,
     *     "from": 1,
     *     "to": 15
     *   },
     *   "filters": {
     *     "search": "John",
     *     "department_id": 1,
     *     "status": "active"
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving employees: Database connection failed"
     * }
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
     * Create New Employee
     *
     * Create a new employee with automatic number generation and comprehensive data validation.
     *
     * @bodyParam company_id integer required The company ID. Example: 1
     * @bodyParam department_id integer required The department ID. Example: 1
     * @bodyParam job_title_id integer required The job title ID. Example: 1
     * @bodyParam currency_id integer required The currency ID. Example: 1
     * @bodyParam name string required The employee full name. Example: John Doe
     * @bodyParam employee_number string optional The employee number (auto-generated if not provided). Example: EMP001
     * @bodyParam email string required The employee email address. Example: john.doe@example.com
     * @bodyParam phone string optional The employee phone number. Example: +1234567890
     * @bodyParam hire_date string required The hire date (YYYY-MM-DD). Example: 2025-01-15
     * @bodyParam salary decimal required The employee salary. Example: 5000.00
     * @bodyParam address string optional The employee address. Example: 123 Main St, City
     * @bodyParam national_id string optional The national ID number. Example: 123456789
     * @bodyParam passport_number string optional The passport number. Example: A12345678
     * @bodyParam birth_date string optional The birth date (YYYY-MM-DD). Example: 1990-05-15
     * @bodyParam gender string optional The gender (male, female). Example: male
     * @bodyParam marital_status string optional The marital status. Example: single
     * @bodyParam emergency_contact_name string optional Emergency contact name. Example: Jane Doe
     * @bodyParam emergency_contact_phone string optional Emergency contact phone. Example: +1234567891
     * @bodyParam status string required The employee status. Example: active
     * @bodyParam notes string optional Additional notes. Example: Experienced developer
     *
     * @response 201 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "employee_number": "EMP001",
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "phone": "+1234567890",
     *     "hire_date": "2025-01-15",
     *     "salary": 5000.00,
     *     "address": "123 Main St, City",
     *     "national_id": "123456789",
     *     "birth_date": "1990-05-15",
     *     "gender": "male",
     *     "status": "active",
     *     "department": {
     *       "id": 1,
     *       "name": "IT Department"
     *     },
     *     "job_title": {
     *       "id": 1,
     *       "name": "Software Developer"
     *     },
     *     "currency": {
     *       "id": 1,
     *       "name": "USD",
     *       "symbol": "$"
     *     },
     *     "created_at": "2025-10-05T10:00:00.000000Z"
     *   },
     *   "message": "Employee created successfully."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "email": ["The email field is required."],
     *     "hire_date": ["The hire date field is required."]
     *   }
     * }
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
     * Show Employee Details
     *
     * Display detailed information for a specific employee including all relationships.
     *
     * @urlParam employee integer required The ID of the employee. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "employee_number": "EMP001",
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "phone": "+1234567890",
     *     "hire_date": "2025-01-15",
     *     "salary": 5000.00,
     *     "address": "123 Main St, City",
     *     "national_id": "123456789",
     *     "passport_number": "A12345678",
     *     "birth_date": "1990-05-15",
     *     "gender": "male",
     *     "marital_status": "single",
     *     "emergency_contact_name": "Jane Doe",
     *     "emergency_contact_phone": "+1234567891",
     *     "status": "active",
     *     "balance": 2500.00,
     *     "notes": "Experienced developer",
     *     "department": {
     *       "id": 1,
     *       "name": "IT Department",
     *       "manager": "Manager Name"
     *     },
     *     "job_title": {
     *       "id": 1,
     *       "name": "Software Developer",
     *       "description": "Develops software applications"
     *     },
     *     "currency": {
     *       "id": 1,
     *       "name": "USD",
     *       "symbol": "$",
     *       "rate": 1.0
     *     },
     *     "created_at": "2025-10-05T10:00:00.000000Z",
     *     "updated_at": "2025-10-05T10:00:00.000000Z"
     *   },
     *   "message": "Employee retrieved successfully."
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "Employee not found"
     * }
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
     * Generate Next Employee Number
     *
     * Generate the next sequential employee number for a specific company, used for auto-numbering new employees.
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "employee_number": "EMP005"
     *   },
     *   "message": "Next employee number generated successfully."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "An error occurred while generating employee number.",
     *   "message": "Database connection failed"
     * }
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
