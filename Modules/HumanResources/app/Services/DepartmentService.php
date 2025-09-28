<?php

namespace Modules\HumanResources\app\Services;

use Illuminate\Support\Facades\Auth;
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
            'created_by' => Auth::id(),
        ]);
    }

    public function update(DepartmentRequest $request, Department $department): Department
    {
        $department->update($request->validated() + [
            'updated_by' => Auth::id(),
        ]);

        return $department;
    }

    public function delete(Department $department): void
    {
        $department->update(['deleted_by' => Auth::id()]);
        $department->delete();
    }
}
