<?php

namespace Modules\HumanResources\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResources\app\Services\DepartmentService;
use Modules\HumanResources\Http\Requests\DepartmentRequest;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Transformers\DerpartmentResource;

/**
 * @group Department Management
 *
 * APIs for managing departments within the Human Resources module, including creation, updates, search, sorting, and department relationship management.
 */
class DepartmentController extends Controller
{

    protected DepartmentService $service;

    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    /**
     * List Departments
     *
     * Retrieve a paginated list of departments with comprehensive filtering and sorting options.
     *
     * @queryParam name string Search by department name (partial match). Example: IT Department
     * @queryParam number_from integer Filter by department number range (from). Example: 1
     * @queryParam number_to integer Filter by department number range (to). Example: 100
     * @queryParam date string Filter by exact creation date (YYYY-MM-DD). Example: 2025-10-05
     * @queryParam date_from string Filter by creation date range (from). Example: 2025-10-01
     * @queryParam date_to string Filter by creation date range (to). Example: 2025-10-31
     * @queryParam proposed_start_date_from string Filter by proposed start date range (from). Example: 2025-10-01
     * @queryParam proposed_start_date_to string Filter by proposed start date range (to). Example: 2025-12-31
     * @queryParam status string Filter by department status. Example: active
     * @queryParam project_status string Filter by project status. Example: inprogress
     * @queryParam sort_by string Sort by field (number, name, created_at, status, etc.). Example: name
     * @queryParam sort_direction string Sort direction (asc, desc). Example: asc
     * @queryParam per_page integer Number of items per page (default: 10). Example: 15
     * @queryParam company_id integer Filter by company ID. Example: 1
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "company_id": 1,
     *       "user_id": 1,
     *       "branch_id": 1,
     *       "fiscal_year_id": 1,
     *       "name": "IT Department",
     *       "number": 1,
     *       "manager_id": 5,
     *       "address": "Building A, Floor 3",
     *       "work_phone": "+1234567890",
     *       "home_phone": "+1234567891",
     *       "fax": "+1234567892",
     *       "statement": "Information Technology Department",
     *       "statement_en": "Information Technology Department",
     *       "parent_id": null,
     *       "funder_id": null,
     *       "project_status": "inprogress",
     *       "status": "active",
     *       "proposed_start_date": "2025-01-01",
     *       "proposed_end_date": "2025-12-31",
     *       "actual_start_date": "2025-01-15",
     *       "actual_end_date": null,
     *       "budget_id": 1,
     *       "notes": "Main IT department handling all technology operations",
     *       "created_by": 1,
     *       "updated_by": 1,
     *       "deleted_by": null,
     *       "created_at": "2025-10-05T10:00:00.000000Z",
     *       "updated_at": "2025-10-05T10:00:00.000000Z",
     *       "company": {
     *         "id": 1,
     *         "name": "Al-Yaseen Company"
     *       },
     *       "manager": {
     *         "id": 5,
     *         "name": "John Doe"
     *       }
     *     }
     *   ],
     *   "links": {
     *     "first": "http://localhost/api/v1/departments/list?page=1",
     *     "last": "http://localhost/api/v1/departments/list?page=1",
     *     "prev": null,
     *     "next": null
     *   },
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 1,
     *     "per_page": 10,
     *     "to": 1,
     *     "total": 1
     *   }
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "message": "Error retrieving departments: Database connection failed"
     * }
     */
    public function index(Request $request)
    {
        return DerpartmentResource::collection($this->service->list($request));
    }

    /**
     * Show Department Details
     *
     * Display detailed information for a specific department including all relationships.
     *
     * @urlParam department integer required The ID of the department. Example: 1
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "company_id": 1,
     *     "user_id": 1,
     *     "branch_id": 1,
     *     "fiscal_year_id": 1,
     *     "name": "IT Department",
     *     "number": 1,
     *     "manager_id": 5,
     *     "address": "Building A, Floor 3",
     *     "work_phone": "+1234567890",
     *     "home_phone": "+1234567891",
     *     "fax": "+1234567892",
     *     "statement": "Information Technology Department",
     *     "statement_en": "Information Technology Department",
     *     "parent_id": null,
     *     "funder_id": null,
     *     "project_status": "inprogress",
     *     "status": "active",
     *     "proposed_start_date": "2025-01-01",
     *     "proposed_end_date": "2025-12-31",
     *     "actual_start_date": "2025-01-15",
     *     "actual_end_date": null,
     *     "budget_id": 1,
     *     "notes": "Main IT department handling all technology operations",
     *     "created_by": 1,
     *     "updated_by": 1,
     *     "deleted_by": null,
     *     "created_at": "2025-10-05T10:00:00.000000Z",
     *     "updated_at": "2025-10-05T10:00:00.000000Z",
     *     "company": {
     *       "id": 1,
     *       "name": "Al-Yaseen Company"
     *     },
     *     "manager": {
     *       "id": 5,
     *       "name": "John Doe",
     *       "email": "john.doe@example.com"
     *     },
     *     "branch": {
     *       "id": 1,
     *       "name": "Main Branch"
     *     },
     *     "budget": {
     *       "id": 1,
     *       "name": "IT Budget 2025",
     *       "amount": 100000
     *     },
     *     "employees": [
     *       {
     *         "id": 10,
     *         "name": "Jane Smith",
     *         "position": "Software Developer"
     *       }
     *     ]
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Department not found"
     * }
     */
    public function show(Department $department)
    {
        $department = $this->service->show($department);
        return new DerpartmentResource($department);
    }

    /**
     * Get First Department
     *
     * Retrieve the first department for initial display purposes, typically used when loading the department management interface.
     *
     * @queryParam company_id integer Filter by company ID. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "company_id": 1,
     *     "user_id": 1,
     *     "branch_id": 1,
     *     "fiscal_year_id": 1,
     *     "name": "IT Department",
     *     "number": 1,
     *     "manager_id": 5,
     *     "address": "Building A, Floor 3",
     *     "work_phone": "+1234567890",
     *     "status": "active",
     *     "project_status": "inprogress",
     *     "notes": "Main IT department handling all technology operations",
     *     "created_at": "2025-10-05T10:00:00.000000Z",
     *     "updated_at": "2025-10-05T10:00:00.000000Z",
     *     "company": {
     *       "id": 1,
     *       "name": "Al-Yaseen Company"
     *     },
     *     "manager": {
     *       "id": 5,
     *       "name": "John Doe"
     *     }
     *   },
     *   "message": "First department retrieved successfully."
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "No departments found.",
     *   "data": null
     * }
     */
    public function first()
    {
        $department = $this->service->first();

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'No departments found.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new DerpartmentResource($department),
            'message' => 'First department retrieved successfully.'
        ]);
    }

    /**
     * Create New Department
     *
     * Create a new department with automatic number generation and date population.
     *
     * @bodyParam company_id integer required The company ID. Example: 1
     * @bodyParam user_id integer required The user ID. Example: 1
     * @bodyParam branch_id integer required The branch ID. Example: 1
     * @bodyParam fiscal_year_id integer required The fiscal year ID. Example: 1
     * @bodyParam name string required The department name. Example: IT Department
     * @bodyParam number integer optional The department number (auto-generated if not provided). Example: 1
     * @bodyParam manager_id integer required The manager user ID. Example: 5
     * @bodyParam address string optional The department address. Example: Building A, Floor 3
     * @bodyParam work_phone string optional The work phone number. Example: +1234567890
     * @bodyParam home_phone string optional The home phone number. Example: +1234567891
     * @bodyParam fax string optional The fax number. Example: +1234567892
     * @bodyParam statement string optional The department statement in Arabic. Example: قسم تكنولوجيا المعلومات
     * @bodyParam statement_en string optional The department statement in English. Example: Information Technology Department
     * @bodyParam parent_id integer optional The parent department ID. Example: null
     * @bodyParam funder_id integer optional The funder ID. Example: null
     * @bodyParam project_status string required The project status. Example: not_started
     * @bodyParam status string required The department status. Example: active
     * @bodyParam proposed_start_date string optional The proposed start date (YYYY-MM-DD). Example: 2025-01-01
     * @bodyParam proposed_end_date string optional The proposed end date (YYYY-MM-DD). Example: 2025-12-31
     * @bodyParam actual_start_date string optional The actual start date (YYYY-MM-DD). Example: 2025-01-15
     * @bodyParam actual_end_date string optional The actual end date (YYYY-MM-DD). Example: null
     * @bodyParam budget_id integer optional The budget ID. Example: 1
     * @bodyParam notes string optional Additional notes. Example: Main IT department handling all technology operations
     *
     * @response 201 {
     *   "data": {
     *     "id": 1,
     *     "company_id": 1,
     *     "user_id": 1,
     *     "branch_id": 1,
     *     "fiscal_year_id": 1,
     *     "name": "IT Department",
     *     "number": 1,
     *     "manager_id": 5,
     *     "address": "Building A, Floor 3",
     *     "work_phone": "+1234567890",
     *     "home_phone": "+1234567891",
     *     "fax": "+1234567892",
     *     "statement": "قسم تكنولوجيا المعلومات",
     *     "statement_en": "Information Technology Department",
     *     "parent_id": null,
     *     "funder_id": null,
     *     "project_status": "not_started",
     *     "status": "active",
     *     "proposed_start_date": "2025-01-01",
     *     "proposed_end_date": "2025-12-31",
     *     "actual_start_date": "2025-01-15",
     *     "actual_end_date": null,
     *     "budget_id": 1,
     *     "notes": "Main IT department handling all technology operations",
     *     "created_by": 1,
     *     "updated_by": null,
     *     "deleted_by": null,
     *     "created_at": "2025-10-05T10:00:00.000000Z",
     *     "updated_at": "2025-10-05T10:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "manager_id": ["The manager id field is required."]
     *   }
     * }
     */
    public function store(DepartmentRequest $request)
    {
        $department = $this->service->create($request);
        return new DerpartmentResource($department);
    }

    /**
     * Update Department
     *
     * Update an existing department with comprehensive field validation and relationship handling.
     *
     * @urlParam department integer required The ID of the department to update. Example: 1
     * @bodyParam company_id integer required The company ID. Example: 1
     * @bodyParam user_id integer required The user ID. Example: 1
     * @bodyParam branch_id integer required The branch ID. Example: 1
     * @bodyParam fiscal_year_id integer required The fiscal year ID. Example: 1
     * @bodyParam name string required The department name. Example: Updated IT Department
     * @bodyParam number integer optional The department number (auto-generated if empty). Example: 1
     * @bodyParam manager_id integer required The manager user ID. Example: 5
     * @bodyParam address string optional The department address. Example: Building B, Floor 2
     * @bodyParam work_phone string optional The work phone number. Example: +1234567890
     * @bodyParam home_phone string optional The home phone number. Example: +1234567891
     * @bodyParam fax string optional The fax number. Example: +1234567892
     * @bodyParam statement string optional The department statement in Arabic. Example: قسم تكنولوجيا المعلومات المحدث
     * @bodyParam statement_en string optional The department statement in English. Example: Updated Information Technology Department
     * @bodyParam parent_id integer optional The parent department ID. Example: null
     * @bodyParam funder_id integer optional The funder ID. Example: null
     * @bodyParam project_status string required The project status. Example: inprogress
     * @bodyParam status string required The department status. Example: active
     * @bodyParam proposed_start_date string optional The proposed start date (YYYY-MM-DD). Example: 2025-01-01
     * @bodyParam proposed_end_date string optional The proposed end date (YYYY-MM-DD). Example: 2025-12-31
     * @bodyParam actual_start_date string optional The actual start date (YYYY-MM-DD). Example: 2025-01-15
     * @bodyParam actual_end_date string optional The actual end date (YYYY-MM-DD). Example: null
     * @bodyParam budget_id integer optional The budget ID. Example: 1
     * @bodyParam notes string optional Additional notes. Example: Updated IT department with new responsibilities
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "company_id": 1,
     *     "user_id": 1,
     *     "branch_id": 1,
     *     "fiscal_year_id": 1,
     *     "name": "Updated IT Department",
     *     "number": 1,
     *     "manager_id": 5,
     *     "address": "Building B, Floor 2",
     *     "work_phone": "+1234567890",
     *     "home_phone": "+1234567891",
     *     "fax": "+1234567892",
     *     "statement": "قسم تكنولوجيا المعلومات المحدث",
     *     "statement_en": "Updated Information Technology Department",
     *     "parent_id": null,
     *     "funder_id": null,
     *     "project_status": "inprogress",
     *     "status": "active",
     *     "proposed_start_date": "2025-01-01",
     *     "proposed_end_date": "2025-12-31",
     *     "actual_start_date": "2025-01-15",
     *     "actual_end_date": null,
     *     "budget_id": 1,
     *     "notes": "Updated IT department with new responsibilities",
     *     "created_by": 1,
     *     "updated_by": 1,
     *     "deleted_by": null,
     *     "created_at": "2025-10-05T10:00:00.000000Z",
     *     "updated_at": "2025-10-05T12:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Department not found"
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "manager_id": ["The manager id field is required."]
     *   }
     * }
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        $department = $this->service->update($request, $department);
        return new DerpartmentResource($department);
    }

    /**
     * Generate Next Department Number
     *
     * Generate the next sequential department number for a specific company, used for auto-numbering new departments.
     *
     * @queryParam company_id integer required The company ID to generate number for. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "department_number": 5
     *   },
     *   "message": "Next department number generated successfully."
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "An error occurred while generating department number.",
     *   "message": "Database connection failed"
     * }
     */
    public function getNextDepartmentNumber()
    {
        try {
            $companyId = request()->company_id;
            $nextNumber = Department::generateDepartmentNumber($companyId);

            return response()->json([
                'success' => true,
                'data' => [
                    'department_number' => $nextNumber
                ],
                'message' => 'Next department number generated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while generating department number.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Department
     *
     * Soft delete a department from the system. The department will be marked as deleted but preserved in the database for audit purposes.
     *
     * @urlParam department integer required The ID of the department to delete. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Department deleted successfully.",
     *   "data": null
     * }
     *
     * @response 404 {
     *   "message": "Department not found"
     * }
     *
     * @response 500 {
     *   "success": false,
     *   "error": "An error occurred while deleting the department.",
     *   "message": "Cannot delete department with active employees"
     * }
     */
    public function destroy(Department $department)
    {
        try {
            $this->service->delete($department);

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting the department.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
