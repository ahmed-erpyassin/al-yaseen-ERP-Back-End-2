<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Customers\app\Services\CustomerService;
use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;
use Modules\Customers\Transformers\CustomerResource;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of the customers with optional pagination.
     */
    public function index(Request $request)
    {
        try {
            // Check if pagination is requested
            $perPage = $request->get('per_page', 15);
            $paginate = $request->get('paginate', true);

            if ($paginate && $perPage > 0) {
                $customers = $this->customerService->getCustomersWithPagination($request, $perPage);
                return response()->json([
                    'success' => true,
                    'data' => CustomerResource::collection($customers->items()),
                    'pagination' => [
                        'current_page' => $customers->currentPage(),
                        'last_page' => $customers->lastPage(),
                        'per_page' => $customers->perPage(),
                        'total' => $customers->total(),
                        'from' => $customers->firstItem(),
                        'to' => $customers->lastItem(),
                    ]
                ], 200);
            } else {
            $customers = $this->customerService->index($request);
            return response()->json([
                'success' => true,
                    'data' => CustomerResource::collection($customers),
                    'count' => $customers->count()
            ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching customers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CustomerRequest $request)
    {
        try {
            $customer = $this->customerService->store($request);
            return response()->json([
                'success' => true,
                'data'    => new CustomerResource($customer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating customer.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Customer $customer)
    {
        try {
            $customer = $this->customerService->show($customer);
            return response()->json([
                'success' => true,
                'data'    => new CustomerResource($customer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching customer.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            $customer = $this->customerService->update($request, $customer);
            return response()->json([
                'success' => true,
                'data'    => new CustomerResource($customer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating customer.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->destroy($customer, Auth::id());
            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting customer.'], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Customer $customer)
    {
        try {
            $customer = $this->customerService->restore($customer);
            return response()->json([
                'success' => true,
                'data'    => new CustomerResource($customer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while restoring customer.'], 500);
        }
    }

    /**
     * Bulk delete customers
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'customer_ids' => 'required|array|min:1',
                'customer_ids.*' => 'integer|exists:customers,id'
            ]);

            $this->customerService->bulkDelete($request->customer_ids, Auth::id());
            
            return response()->json([
                'success' => true,
                'message' => 'Customers deleted successfully',
                'deleted_count' => count($request->customer_ids)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while bulk deleting customers.'], 500);
        }
    }

    /**
     * Bulk restore customers
     */
    public function bulkRestore(Request $request)
    {
        try {
            $request->validate([
                'customer_ids' => 'required|array|min:1',
                'customer_ids.*' => 'integer|exists:customers,id'
            ]);

            $restoredCount = 0;
            foreach ($request->customer_ids as $customerId) {
                $customer = Customer::withTrashed()->findOrFail($customerId);
                $this->customerService->restore($customer);
                $restoredCount++;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Customers restored successfully',
                'restored_count' => $restoredCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while bulk restoring customers.'], 500);
        }
    }

    /**
     * Advanced search customers with comprehensive filters
     */
    public function advancedSearch(Request $request)
    {
        try {
            // Validate search parameters
            $request->validate([
                'customer_number_from' => 'nullable|string',
                'customer_number_to' => 'nullable|string',
                'customer_name' => 'nullable|string|max:255',
                'last_transaction_date' => 'nullable|date',
                'last_transaction_date_from' => 'nullable|date',
                'last_transaction_date_to' => 'nullable|date|after_or_equal:last_transaction_date_from',
                'sales_representative' => 'nullable|integer|exists:users,id',
                'currency_id' => 'nullable|integer|exists:currencies,id',
                'status' => 'nullable|in:active,inactive',
                'company_id' => 'nullable|integer|exists:companies,id',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'category' => 'nullable|string',
                'country_id' => 'nullable|integer|exists:countries,id',
                'region_id' => 'nullable|integer|exists:regions,id',
                'city_id' => 'nullable|integer|exists:cities,id',
                'created_from' => 'nullable|date',
                'created_to' => 'nullable|date|after_or_equal:created_from',
                'sort_by' => 'nullable|string|in:id,customer_number,company_name,first_name,second_name,email,phone,mobile,status,created_at,updated_at',
                'sort_order' => 'nullable|string|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
                'paginate' => 'nullable|boolean'
            ]);

            $perPage = $request->get('per_page', 15);
            $paginate = $request->get('paginate', true);

            if ($paginate && $perPage > 0) {
                $customers = $this->customerService->getCustomersWithPagination($request, $perPage);
                return response()->json([
                    'success' => true,
                    'data' => CustomerResource::collection($customers->items()),
                    'pagination' => [
                        'current_page' => $customers->currentPage(),
                        'last_page' => $customers->lastPage(),
                        'per_page' => $customers->perPage(),
                        'total' => $customers->total(),
                        'from' => $customers->firstItem(),
                        'to' => $customers->lastItem(),
                    ],
                    'filters_applied' => $request->except(['per_page', 'paginate'])
                ], 200);
            } else {
                $customers = $this->customerService->index($request);
                return response()->json([
                    'success' => true,
                    'data' => CustomerResource::collection($customers),
                    'count' => $customers->count(),
                    'filters_applied' => $request->except(['per_page', 'paginate'])
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching customers.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers (legacy method for backward compatibility)
     */
    public function search($query)
    {
        try {
            $customers = Customer::with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ])->where(function ($q) use ($query) {
                $q->where('first_name', 'like', '%' . $query . '%')
                  ->orWhere('second_name', 'like', '%' . $query . '%')
                  ->orWhere('company_name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%')
                  ->orWhere('customer_number', 'like', '%' . $query . '%')
                  ->orWhere('phone', 'like', '%' . $query . '%')
                  ->orWhere('mobile', 'like', '%' . $query . '%');
            })->get();

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers),
                'query' => $query,
                'count' => $customers->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while searching customers.'], 500);
        }
    }

    /**
     * Filter customers by status
     */
    public function filterByStatus($status)
    {
        try {
            if (!in_array($status, ['active', 'inactive'])) {
                return response()->json(['error' => 'Invalid status. Must be active or inactive.'], 400);
            }

            $customers = Customer::where('status', $status)->get();

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers),
                'status' => $status,
                'count' => $customers->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while filtering customers by status.'], 500);
        }
    }

    /**
     * Filter customers by company
     */
    public function filterByCompany($companyId)
    {
        try {
            $customers = Customer::where('company_id', $companyId)->get();

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers),
                'company_id' => $companyId,
                'count' => $customers->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while filtering customers by company.'], 500);
        }
    }

    /**
     * Get customer statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_customers' => Customer::count(),
                'active_customers' => Customer::where('status', 'active')->count(),
                'inactive_customers' => Customer::where('status', 'inactive')->count(),
                'deleted_customers' => Customer::onlyTrashed()->count(),
                'customers_this_month' => Customer::whereMonth('created_at', now()->month)->count(),
                'customers_this_year' => Customer::whereYear('created_at', now()->year)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching customer statistics.'], 500);
        }
    }

    /**
     * Export customers to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // This would require implementing Excel export functionality
            // For now, return a placeholder response
            return response()->json([
                'success' => true,
                'message' => 'Excel export functionality will be implemented',
                'download_url' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while exporting customers to Excel.'], 500);
        }
    }

    /**
     * Import customers from Excel
     */
    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240' // 10MB max
            ]);

            // This would require implementing Excel import functionality
            // For now, return a placeholder response
            return response()->json([
                'success' => true,
                'message' => 'Excel import functionality will be implemented',
                'imported_count' => 0
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while importing customers from Excel.'], 500);
        }
    }

    /**
     * Get customers sorted by specific field
     */
    public function sortByField(Request $request, $field)
    {
        try {
            $request->validate([
                'order' => 'nullable|string|in:asc,desc'
            ]);

            $allowedFields = [
                'id', 'customer_number', 'company_name', 'first_name', 'second_name',
                'email', 'phone', 'mobile', 'status', 'created_at', 'updated_at',
                'contact_name', 'address_one', 'postal_code', 'tax_number', 'category'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'error' => 'Invalid sort field',
                    'allowed_fields' => $allowedFields
                ], 400);
            }

            $order = $request->get('order', 'asc');
            $perPage = $request->get('per_page', 15);

            $customers = Customer::with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ])
            ->orderBy($field, $order)
            ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers->items()),
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ],
                'sort_info' => [
                    'field' => $field,
                    'order' => $order
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while sorting customers.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer details by specific field value
     */
    public function getByField(Request $request, $field, $value)
    {
        try {
            $allowedFields = [
                'id', 'customer_number', 'company_name', 'first_name', 'second_name',
                'email', 'phone', 'mobile', 'contact_name', 'tax_number'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'error' => 'Invalid search field',
                    'allowed_fields' => $allowedFields
                ], 400);
            }

            $customers = Customer::with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter'
            ])->where($field, 'like', '%' . $value . '%')->get();

            return response()->json([
                'success' => true,
                'data' => CustomerResource::collection($customers),
                'search_info' => [
                    'field' => $field,
                    'value' => $value
                ],
                'count' => $customers->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching customers by field.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available field values for filtering
     */
    public function getFieldValues($field)
    {
        try {
            $allowedFields = [
                'status', 'category', 'invoice_type', 'country_id', 'region_id',
                'city_id', 'currency_id', 'employee_id'
            ];

            if (!in_array($field, $allowedFields)) {
                return response()->json([
                    'error' => 'Invalid field for values extraction',
                    'allowed_fields' => $allowedFields
                ], 400);
            }

            $values = Customer::select($field)
                ->distinct()
                ->whereNotNull($field)
                ->pluck($field)
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'field' => $field,
                'values' => $values,
                'count' => $values->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching field values.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers with last transaction information
     */
    public function getWithLastTransaction(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);

            $customers = Customer::with([
                'user', 'company', 'currency', 'country', 'region', 'city',
                'employee', 'creator', 'updater', 'deleter', 'sales', 'invoices'
            ])->paginate($perPage);

            $customersWithLastTransaction = $customers->getCollection()->map(function ($customer) {
                $customerArray = (new CustomerResource($customer))->toArray(request());
                $customerArray['last_transaction_date'] = $customer->last_transaction_date;
                return $customerArray;
            });

            return response()->json([
                'success' => true,
                'data' => $customersWithLastTransaction,
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching customers with transaction data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dropdown data for customer form.
     */
    public function getFormData()
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id ?? request()->company_id;

            $data = [
                'currencies' => \Modules\FinancialAccounts\Models\Currency::select('id', 'name', 'code', 'symbol')
                    ->where('company_id', $companyId)
                    ->get(),

                'sales_representatives' => \Modules\HumanResources\Models\Employee::select('id', 'employee_number', 'first_name', 'second_name')
                    ->where('company_id', $companyId)
                    ->where('is_sales', true)
                    ->get()
                    ->map(function ($employee) {
                        return [
                            'id' => $employee->id,
                            'employee_number' => $employee->employee_number,
                            'full_name' => trim($employee->first_name . ' ' . $employee->second_name),
                            'display_name' => $employee->employee_number . ' - ' . trim($employee->first_name . ' ' . $employee->second_name)
                        ];
                    }),

                'branches' => \Modules\Companies\Models\Branch::select('id', 'name', 'code')
                    ->where('company_id', $companyId)
                    ->get(),

                'countries' => \Modules\Companies\Models\Country::select('id', 'name')->get(),

                'regions' => \Modules\Companies\Models\Region::select('id', 'name', 'country_id')->get(),

                'cities' => \Modules\Companies\Models\City::select('id', 'name', 'region_id')->get(),

                'barcode_types' => Customer::getAvailableBarcodeTypes(),

                'customer_types' => Customer::CUSTOMER_TYPE_OPTIONS,

                'category_options' => Customer::CATEGORY_OPTIONS,

                'next_customer_number' => Customer::generateCustomerNumber(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching form data.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get next customer number.
     */
    public function getNextCustomerNumber()
    {
        try {
            $nextNumber = Customer::generateCustomerNumber();

            return response()->json([
                'success' => true,
                'customer_number' => $nextNumber
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating customer number.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales representatives for dropdown.
     */
    public function getSalesRepresentatives()
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id ?? request()->company_id;

            $salesReps = \Modules\HumanResources\Models\Employee::select('id', 'employee_number', 'first_name', 'second_name')
                ->where('company_id', $companyId)
                ->where('is_sales', true)
                ->get()
                ->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'employee_number' => $employee->employee_number,
                        'full_name' => trim($employee->first_name . ' ' . $employee->second_name),
                        'display_name' => $employee->employee_number . ' - ' . trim($employee->first_name . ' ' . $employee->second_name)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $salesReps
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching sales representatives.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
