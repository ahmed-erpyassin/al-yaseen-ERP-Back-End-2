<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index()
    {
        return response()->json(Employee::all(), 200);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('attachments')) {
            $data['attachments'] = $request->file('attachments')->store('employees', 'public');
        }

        $employee = Employee::create($data);

        return response()->json($employee, 201);
    }
}
