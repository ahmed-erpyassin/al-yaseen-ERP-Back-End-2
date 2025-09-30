<?php

namespace Modules\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customers\app\Services\CustomerService;
use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Transformers\CustomerResource;

class CustomerController extends Controller
{
    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Store a newly created customer in storage.
     * Creates a new customer with comprehensive validation and audit trail.
     *
     * @param CustomerRequest $request Validated customer data
     * @return CustomerResource|JsonResponse Customer resource or error response
     */
    public function store(CustomerRequest $request)
    {
        try {
            DB::beginTransaction();
            $customer = $this->customerService->createCustomer($request->validated(), $request->user());
            DB::commit();
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of customers with advanced search and filtering.
     * Supports pagination, sorting, and comprehensive search across multiple fields.
     *
     * @param Request $request Request parameters for filtering and pagination
     * @return CustomerResource[]|JsonResponse Collection of customers or error response
     */
    public function index(Request $request)
    {
        try {
            $customers = $this->customerService->getCustomers($request->user());
            return CustomerResource::collection($customers);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified customer with all related data.
     * Returns customer details with relationships loaded for comprehensive view.
     *
     * @param int $id Customer ID
     * @return CustomerResource|JsonResponse Customer resource or error response
     */
    public function show($id)
    {
        try {
            $customer = $this->customerService->getCustomerById($id);
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified customer in storage.
     * Updates customer data with comprehensive validation and relationship handling.
     *
     * @param CustomerRequest $request Validated customer data
     * @param int $id Customer ID
     * @return CustomerResource|JsonResponse Updated customer resource or error response
     */
    public function update(CustomerRequest $request, $id)
    {
        try {
            $customer = $this->customerService->updateCustomer($id, $request->validated());
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified customer from storage (soft delete).
     * Performs soft delete with audit trail tracking who deleted the customer.
     *
     * @param int $id Customer ID
     * @return JsonResponse Success message or error response
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $this->customerService->deleteCustomer($id, $user->id);
            return response()->json(['message' => 'Customer deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted customer.
     * Restores a previously deleted customer and returns updated resource.
     *
     * @param int $id Customer ID
     * @return CustomerResource|JsonResponse Restored customer resource or error response
     */
    public function restore($id)
    {
        try {
            $customer = $this->customerService->restoreCustomer($id);
            return new CustomerResource($customer);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to restore customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete multiple customers.
     * Performs soft delete on multiple customers with audit trail.
     *
     * @param Request $request Request containing array of customer IDs
     * @return JsonResponse Success message or error response
     */
    public function bulkDelete(Request $request)
    {
        try {
            $user = Auth::user();
            $this->customerService->bulkDelete($request->ids, $user->id);
            return response()->json(['message' => 'Customers deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
