<?php

namespace Modules\HumanResources\app\Services;

use Modules\HumanResources\Http\Requests\DepartmentRequest;
use Modules\HumanResources\Models\Department;

class DepartmentService
{


    public function list()
    {
        return Department::paginate(10);
    }

    public function create(DepartmentRequest $request): Department
    {
        return Department::create($request->validated() + [
            'created_by' => auth()->id(),
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): Department
    {
        $department->update($request->validated() + [
            'updated_by' => auth()->id(),
        ]);

        return $department;
    }

    public function delete(Department $department): void
    {
        $department->update(['deleted_by' => auth()->id()]);
        $department->delete();
    }
}
