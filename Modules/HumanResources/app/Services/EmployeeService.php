<?php

namespace Modules\HumanResources\app\Services;

use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\Http\Requests\EmployeeRequest;
use Modules\HumanResources\Models\Employee;

class EmployeeService
{
    public function list()
    {
        return Employee::paginate(10);
    }

    public function create(EmployeeRequest $request): Employee
    {
        return Employee::create($request->validated() + [
            'created_by' => Auth::id(),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): Employee
    {
        $employee->update($request->validated() + [
            'updated_by' => Auth::id(),
        ]);

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->update(['deleted_by' => Auth::id()]);
        $employee->delete();
    }
}
