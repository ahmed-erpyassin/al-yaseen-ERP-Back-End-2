<?php

namespace Modules\HumanResources\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\HumanResources\app\Services\DepartmentService;
use Modules\HumanResources\Http\Requests\DepartmentRequest;
use Modules\HumanResources\Models\Department;
use Modules\HumanResources\Transformers\DerpartmentResource;

class DepartmentController extends Controller
{

    protected DepartmentService $service;

    public function __construct(DepartmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the departments.
     */
    public function index(Request $request)
    {
        return DerpartmentResource::collection($this->service->list($request));
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        $department = $this->service->show($department);
        return new DerpartmentResource($department);
    }

    /**
     * Get the first department for initial display.
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
     * Store a newly created department in storage.
     */
    public function store(DepartmentRequest $request)
    {
        $department = $this->service->create($request);
        return new DerpartmentResource($department);
    }

    /**
     * Update the specified department in storage.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        $department = $this->service->update($request, $department);
        return new DerpartmentResource($department);
    }

    /**
     * Get next department number
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
     * Remove the specified department from storage (soft delete).
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
