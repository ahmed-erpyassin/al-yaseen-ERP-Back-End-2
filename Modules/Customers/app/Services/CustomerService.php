<?php

namespace Modules\Customers\app\Services;

use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;

class CustomerService
{
    public function index($request)
    {
        try {

            $customerSearch = $request->get('customerSearch', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Customer::query()
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->where(function ($q) use ($customerSearch) {
                        $q->where('first_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('second_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('company_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('email', 'like', '%' . $customerSearch . '%')
                          ->orWhere('customer_number', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customers: ' . $e->getMessage());
        }
    }

    public function store(CustomerRequest $request)
    {

        try {
            $companyId = $request->user()->company_id ?? $request->company_id;
            $userId = $request->user()->id;

            $data = [
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'active',
            ] + $request->validated();

            $customer = Customer::create($data);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error creating customer: ' . $e->getMessage());
        }
    }
    public function show(Customer $customer)
    {
        try {
            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customer: ' . $e->getMessage());
        }
    }
    public function update(CustomerRequest $request, Customer $customer)
    {
        try {
            $data = $request->validated();
            // Add updated_by information
            $data['updated_by'] = $request->user()->id;

            $customer->update($data);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error updating customer: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer, $userId = null)
    {
        try {
            // Add deleted_by information before soft delete
            $customer->update(['deleted_by' => $userId]);
            $customer->delete();
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting customer: ' . $e->getMessage());
        }
    }


    public function restore(Customer $customer)
    {
        try {
            $customer->restore();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error restoring customer: ' . $e->getMessage());
        }
    }

    public function findById($id)
    {
        try {
            return Customer::findOrFail($id);
        } catch (\Exception $e) {
            throw new \Exception('Customer not found: ' . $e->getMessage());
        }
    }

    public function getCustomersWithPagination($request, $perPage = 10)
    {
        try {
            $customerSearch = $request->get('customerSearch', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Customer::query()
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->where(function ($q) use ($customerSearch) {
                        $q->where('first_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('second_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('company_name', 'like', '%' . $customerSearch . '%')
                          ->orWhere('email', 'like', '%' . $customerSearch . '%')
                          ->orWhere('customer_number', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching customers with pagination: ' . $e->getMessage());
        }
    }

    public function bulkDelete($customerIds, $userId = null)
    {
        try {
            $customers = Customer::whereIn('id', $customerIds)->get();
            
            foreach ($customers as $customer) {
                $customer->update(['deleted_by' => $userId]);
                $customer->delete();
            }
            
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error bulk deleting customers: ' . $e->getMessage());
        }
    }
}
