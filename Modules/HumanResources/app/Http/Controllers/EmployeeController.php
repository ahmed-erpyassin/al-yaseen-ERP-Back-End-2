<?php

namespace Modules\HumanResources\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\HumanResources\Models\Employee;
use Modules\HumanResources\app\Services\EmployeeService;
use Modules\HumanResources\Http\Requests\EmployeeRequest;
use Modules\HumanResources\Transformers\EmployeeResource;

class EmployeeController extends Controller
{
    protected EmployeeService $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        return EmployeeResource::collection($this->service->list());
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(EmployeeRequest $request)
    {
        $employee = $this->service->create($request);
        return new EmployeeResource($employee);
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        return new EmployeeResource($employee);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $employee = $this->service->update($request, $employee);
        return new EmployeeResource($employee);
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $this->service->delete($employee);
        return response()->noContent();
    }
}
