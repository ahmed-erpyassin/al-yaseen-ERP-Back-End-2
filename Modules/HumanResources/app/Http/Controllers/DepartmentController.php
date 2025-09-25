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
    public function index()
    {
        return DerpartmentResource::collection($this->service->list());
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
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        $this->service->delete($department);
        return response()->noContent();
    }
}
