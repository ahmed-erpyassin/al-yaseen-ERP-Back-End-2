<?php

namespace Modules\HumanResources\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\HumanResources\Models\Employee;
use Modules\FinancialAccounts\Models\Account;
use Modules\FinancialAccounts\Models\Currency;

/**
 * @group Employee/Payroll Search
 *
 * APIs for searching and filtering employees, accounts, and currencies specifically for payroll operations.
 */
class PayrollSearchController extends Controller
{
    /**
     * Search Employees for Payroll
     *
     * Search and filter employees specifically for payroll processing operations.
     *
     * @bodyParam company_id integer required The company ID to search within. Example: 1
     * @bodyParam search string optional Search query for employee name or number. Example: John
     * @bodyParam limit integer optional Maximum number of results (default: 20, max: 100). Example: 50
     *
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "employee_number": "EMP001",
     *       "full_name": "John Doe",
     *       "department": {
     *         "id": 1,
     *         "name": "IT Department"
     *       },
     *       "job_title": {
     *         "id": 1,
     *         "name": "Software Developer"
     *       },
     *       "salary": 5000.00,
     *       "status": "active"
     *     }
     *   ],
     *   "message": "Employees retrieved successfully."
     * }
     *
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "company_id": ["The company id field is required."]
     *   }
     * }
     */
    public function searchEmployees(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Employee::forCompany($request->company_id)
                ->with(['jobTitle', 'department']);

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('employee_number', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('second_name', 'like', "%{$search}%")
                      ->orWhere('third_name', 'like', "%{$search}%")
                      ->orWhereRaw("CONCAT(first_name, ' ', COALESCE(second_name, ''), ' ', COALESCE(third_name, ''), ' ', last_name) LIKE ?", ["%{$search}%"]);
                });
            }

            $employees = $query->orderBy('employee_number')
                ->limit($request->get('limit', 20))
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_number' => $employee->employee_number,
                        'full_name' => $employee->full_name,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                        'national_id' => $employee->national_id,
                        'email' => $employee->email,
                        'phone1' => $employee->phone1,
                        'hire_date' => $employee->hire_date?->format('Y-m-d'),
                        'salary' => $employee->salary,
                        'marital_status' => $employee->marital_status,
                        'job_title' => [
                            'id' => $employee->job_title_id,
                            'name' => $employee->jobTitle?->name,
                        ],
                        'department' => [
                            'id' => $employee->department_id,
                            'name' => $employee->department?->name,
                        ],
                        'duration' => $employee->hire_date ?
                            $employee->hire_date->diffInYears(now()) . ' years, ' .
                            ($employee->hire_date->diffInMonths(now()) % 12) . ' months' : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employees retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to search employees.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee by number
     */
    public function getEmployeeByNumber(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'employee_number' => 'required|string'
            ]);

            $employee = Employee::forCompany($request->company_id)
                ->where('employee_number', $request->employee_number)
                ->with(['jobTitle', 'department'])
                ->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'error' => 'Employee not found.',
                    'message' => 'No employee found with the specified number.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'full_name' => $employee->full_name,
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'national_id' => $employee->national_id,
                    'email' => $employee->email,
                    'phone1' => $employee->phone1,
                    'hire_date' => $employee->hire_date?->format('Y-m-d'),
                    'salary' => $employee->salary,
                    'marital_status' => $employee->marital_status,
                    'job_title' => [
                        'id' => $employee->job_title_id,
                        'name' => $employee->jobTitle?->name,
                    ],
                    'department' => [
                        'id' => $employee->department_id,
                        'name' => $employee->department?->name,
                    ],
                    'duration' => $employee->hire_date ?
                        $employee->hire_date->diffInYears(now()) . ' years, ' .
                        ($employee->hire_date->diffInMonths(now()) % 12) . ' months' : null,
                ],
                'message' => 'Employee retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get employee.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search accounts for payroll
     */
    public function searchAccounts(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $query = Account::where('company_id', $request->company_id);

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            }

            $accounts = $query->orderBy('code')
                ->limit($request->get('limit', 20))
                ->get()
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'code' => $account->code,
                        'name' => $account->name,
                        'type' => $account->type,
                        'display_name' => $account->code . ' - ' . $account->name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'message' => 'Accounts retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to search accounts.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account by code
     */
    public function getAccountByCode(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'account_code' => 'required|string'
            ]);

            $account = Account::where('company_id', $request->company_id)
                ->where('code', $request->account_code)
                ->first();

            if (!$account) {
                return response()->json([
                    'success' => false,
                    'error' => 'Account not found.',
                    'message' => 'No account found with the specified code.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'type' => $account->type,
                    'display_name' => $account->code . ' - ' . $account->name,
                ],
                'message' => 'Account retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get account.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies dropdown
     */
    public function getCurrencies(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id'
            ]);

            $currencies = Currency::where('company_id', $request->company_id)
                ->orderBy('code')
                ->get()
                ->map(function ($currency) {
                    return [
                        'id' => $currency->id,
                        'code' => $currency->code,
                        'name' => $currency->name,
                        'symbol' => $currency->symbol,
                        'display_name' => $currency->code . ' - ' . $currency->name,
                        'is_base_currency' => $currency->is_base_currency ?? false,
                        'exchange_rate' => $currency->exchange_rate ?? 1.0,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $currencies,
                'message' => 'Currencies retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get currencies.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee numbers dropdown
     */
    public function getEmployeeNumbers(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id'
            ]);

            $employees = Employee::forCompany($request->company_id)
                ->orderBy('employee_number')
                ->get(['id', 'employee_number', 'first_name', 'last_name', 'second_name', 'third_name'])
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_number' => $employee->employee_number,
                        'full_name' => $employee->full_name,
                        'display_name' => $employee->employee_number . ' - ' . $employee->full_name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Employee numbers retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get employee numbers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account numbers dropdown
     */
    public function getAccountNumbers(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id'
            ]);

            $accounts = Account::where('company_id', $request->company_id)
                ->orderBy('code')
                ->get(['id', 'code', 'name'])
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'code' => $account->code,
                        'name' => $account->name,
                        'display_name' => $account->code . ' - ' . $account->name,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $accounts,
                'message' => 'Account numbers retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get account numbers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get employee selection options for all fields
     */
    public function getEmployeeSelectionOptions(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id'
            ]);

            $employees = Employee::forCompany($request->company_id)
                ->with(['jobTitle', 'department', 'manager'])
                ->get();

            // Get unique values for each field
            $selectionOptions = [
                'employee_numbers' => $employees->pluck('employee_number')->unique()->filter()->values(),
                'employee_names' => $employees->map(function ($emp) {
                    return $emp->full_name;
                })->unique()->filter()->values(),
                'national_ids' => $employees->pluck('national_id')->unique()->filter()->values(),
                'marital_statuses' => $employees->map(function ($emp) {
                    return $emp->marital_status;
                })->unique()->filter()->values(),
                'job_titles' => $employees->map(function ($emp) {
                    return $emp->jobTitle?->name;
                })->unique()->filter()->values(),
                'departments' => $employees->map(function ($emp) {
                    return $emp->department?->name;
                })->unique()->filter()->values(),
                'genders' => $employees->pluck('gender')->unique()->filter()->values(),
                'hire_years' => $employees->map(function ($emp) {
                    return $emp->hire_date?->year;
                })->unique()->filter()->values(),
                'salary_ranges' => [
                    '0-1000' => '0 - 1,000',
                    '1001-3000' => '1,001 - 3,000',
                    '3001-5000' => '3,001 - 5,000',
                    '5001-10000' => '5,001 - 10,000',
                    '10001+' => '10,001+'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $selectionOptions,
                'message' => 'Employee selection options retrieved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get employee selection options.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payroll data based on employee field selection
     */
    public function getPayrollDataByEmployeeSelection(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'company_id' => 'required|integer|exists:companies,id',
                'payroll_record_id' => 'required|integer|exists:payroll_records,id',
                'selection_field' => 'required|string|in:employee_number,employee_name,national_id,marital_status,job_title,department,gender,hire_year,salary_range',
                'selection_value' => 'required|string'
            ]);

            $payrollRecord = PayrollRecord::find($request->payroll_record_id);

            if ($payrollRecord->company_id !== $request->company_id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access.',
                    'message' => 'Payroll record does not belong to your company.'
                ], 403);
            }

            $query = $payrollRecord->payrollData()
                ->with(['employee.jobTitle', 'employee.department']);

            // Apply selection filter based on field
            switch ($request->selection_field) {
                case 'employee_number':
                    $query->where('employee_number', $request->selection_value);
                    break;
                case 'employee_name':
                    $query->where('employee_name', 'like', '%' . $request->selection_value . '%');
                    break;
                case 'national_id':
                    $query->where('national_id', $request->selection_value);
                    break;
                case 'marital_status':
                    $query->where('marital_status', $request->selection_value);
                    break;
                case 'job_title':
                    $query->where('job_title', $request->selection_value);
                    break;
                case 'department':
                    $query->whereHas('employee.department', function ($q) use ($request) {
                        $q->where('name', $request->selection_value);
                    });
                    break;
                case 'gender':
                    $query->whereHas('employee', function ($q) use ($request) {
                        $q->where('gender', $request->selection_value);
                    });
                    break;
                case 'hire_year':
                    $query->whereHas('employee', function ($q) use ($request) {
                        $q->whereYear('hire_date', $request->selection_value);
                    });
                    break;
                case 'salary_range':
                    $ranges = [
                        '0-1000' => [0, 1000],
                        '1001-3000' => [1001, 3000],
                        '3001-5000' => [3001, 5000],
                        '5001-10000' => [5001, 10000],
                        '10001+' => [10001, 999999999]
                    ];
                    if (isset($ranges[$request->selection_value])) {
                        $range = $ranges[$request->selection_value];
                        $query->whereBetween('basic_salary', $range);
                    }
                    break;
            }

            $payrollData = $query->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'payroll_data' => $payrollData,
                    'selection_info' => [
                        'field' => $request->selection_field,
                        'value' => $request->selection_value,
                        'count' => $payrollData->count()
                    ],
                    'totals' => [
                        'total_basic_salary' => $payrollData->sum('basic_salary'),
                        'total_income_tax' => $payrollData->sum('income_tax'),
                        'total_salary_for_payment' => $payrollData->sum('salary_for_payment'),
                        'total_paid_in_cash' => $payrollData->sum('paid_in_cash')
                    ]
                ],
                'message' => 'Payroll data retrieved based on employee selection successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get payroll data by employee selection.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
