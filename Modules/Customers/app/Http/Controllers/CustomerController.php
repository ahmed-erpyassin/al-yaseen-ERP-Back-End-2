<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
            $this->customerService->destroy($customer, auth()->id());
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

            $this->customerService->bulkDelete($request->customer_ids, auth()->id());
            
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
     * Search customers
     */
    public function search($query)
    {
        try {
            $customers = Customer::where(function ($q) use ($query) {
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
}
