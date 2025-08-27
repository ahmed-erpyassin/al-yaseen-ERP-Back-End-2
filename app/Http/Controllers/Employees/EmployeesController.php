<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeesController extends Controller
{
    public function index(Request $request)
    {

        $user_id = $request->user()->id;

        $employees = Employee::where('user_id', $user_id)->get();

        return response()->json([
            'success' => true,
            'data'    => $employees
        ], 200);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        $employee = Employee::create($data);

        return response()->json([
            'success' => true,
            'data'    => $employee
        ], 201);
    }
}
