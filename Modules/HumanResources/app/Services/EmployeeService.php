<?php

namespace Modules\HumanResources\app\Services;

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
            'created_by' => auth()->id(),
        ]);
    }

    public function update(EmployeeRequest $request, Employee $employee): Employee
    {
        $employee->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return $employee;
    }

    public function delete(Employee $employee): void
    {
        $employee->update(['deleted_by' => auth()->id()]);
        $employee->delete();
    }
}
