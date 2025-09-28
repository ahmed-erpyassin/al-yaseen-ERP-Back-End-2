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
