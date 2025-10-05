<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\app\Services\Employee\EmployeeService;
use Modules\HumanResources\Transformers\Employee\EmployeeResource;

/**
 * @group Employee/Employee Search
 *
 * APIs for advanced employee search functionality, including quick search, statistics, and export capabilities.
 */
class EmployeeSearchController extends Controller
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    /**
     * Get search form data (departments, currencies, etc.)
     */
    public function getSearchFormData(): JsonResponse
    {
        try {
            $formData = $this->service->getFormData();

            // Add additional search-specific data
            $searchData = [
                'departments' => $formData['departments'],
                'currencies' => $formData['currencies'],
                'branches' => $formData['branches'],
                'job_titles' => $formData['job_titles'],
                'gender_options' => $formData['gender_options'],
                'employee_types' => $formData['employee_types'],
                'sort_options' => [
                    ['value' => 'employee_number', 'label' => 'Employee Number'],
                    ['value' => 'first_name', 'label' => 'First Name'],
                    ['value' => 'last_name', 'label' => 'Last Name'],
                    ['value' => 'hire_date', 'label' => 'Hire Date'],
                    ['value' => 'birth_date', 'label' => 'Birth Date'],
                    ['value' => 'salary', 'label' => 'Salary'],
                    ['value' => 'balance', 'label' => 'Balance'],
                    ['value' => 'created_at', 'label' => 'Created Date'],
                    ['value' => 'updated_at', 'label' => 'Updated Date'],
                ],
                'sort_directions' => [
                    ['value' => 'asc', 'label' => 'Ascending'],
                    ['value' => 'desc', 'label' => 'Descending'],
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $searchData,
                'message' => 'Search form data retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick Search Employees
     *
     * Perform a quick search for employees by name or employee number for autocomplete functionality.
     *
     * @bodyParam query string required The search query (name or employee number). Example: John
     * @bodyParam limit integer optional Maximum number of results to return (default: 10, max: 50). Example: 15
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "employee_number": "EMP001",
     *       "name": "John Doe",
     *       "email": "john.doe@example.com",
     *       "department": {
     *         "id": 1,
     *         "name": "IT Department"
     *       },
     *       "job_title": {
     *         "id": 1,
     *         "name": "Software Developer"
     *       },
     *       "status": "active"
     *     }
     *   ],
     *   "message": "Quick search completed successfully."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "query": ["The query field is required."]
     *   }
     * }
     */
    public function quickSearch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'query' => 'required|string|min:1|max:255',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $companyId = Auth::user()->company->id;
            $query = $request->get('query');
            $limit = $request->get('limit', 10);

            $employees = \Modules\HumanResources\Models\Employee::with(['department', 'jobTitle', 'currency'])
                ->forCompany($companyId)
                ->where(function ($q) use ($query) {
                    $q->where('employee_number', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('second_name', 'like', "%{$query}%")
                      ->orWhere('third_name', 'like', "%{$query}%")
                      ->orWhere('nickname', 'like', "%{$query}%");
                })
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => EmployeeResource::collection($employees),
                'total' => $employees->count(),
                'query' => $query,
                'message' => 'Quick search completed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred during quick search.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee statistics for dashboard
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $companyId = Auth::user()->company->id;

            $stats = [
                'total_employees' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->count(),
                'active_employees' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->whereNull('deleted_at')->count(),
                'drivers' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->drivers()->count(),
                'sales_reps' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->salesReps()->count(),
                'male_employees' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->where('gender', 'male')->count(),
                'female_employees' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->where('gender', 'female')->count(),
                'employees_with_positive_balance' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->where('balance', '>', 0)->count(),
                'employees_with_negative_balance' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->where('balance', '<', 0)->count(),
                'recent_hires' => \Modules\HumanResources\Models\Employee::forCompany($companyId)
                    ->where('hire_date', '>=', now()->subDays(30))
                    ->count(),
                'average_salary' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->avg('salary'),
                'total_balance' => \Modules\HumanResources\Models\Employee::forCompany($companyId)->sum('balance'),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Employee statistics retrieved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching statistics.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export employees based on search criteria
     */
    public function exportEmployees(Request $request): JsonResponse
    {
        try {
            // Validate export request
            $request->validate([
                'format' => 'required|in:csv,excel,pdf',
                'fields' => 'nullable|array',
                'fields.*' => 'string'
            ]);

            // Get employees based on search criteria
            $employees = $this->service->list($request);

            // Default fields to export
            $defaultFields = [
                'employee_number', 'full_name', 'email', 'phone1',
                'department', 'job_title', 'hire_date', 'salary', 'balance'
            ];

            $fieldsToExport = $request->get('fields', $defaultFields);

            // For now, return the data that would be exported
            // In a real implementation, you would generate the actual file
            return response()->json([
                'success' => true,
                'data' => [
                    'total_records' => $employees->total(),
                    'format' => $request->get('format'),
                    'fields' => $fieldsToExport,
                    'preview' => EmployeeResource::collection($employees->items()->take(5)),
                ],
                'message' => 'Export data prepared successfully. (Implementation pending)'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while preparing export.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
