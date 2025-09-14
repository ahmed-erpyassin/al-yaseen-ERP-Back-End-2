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
                    $query->where('name', 'like', '%' . $customerSearch . '%');
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
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
            throw new \Exception('Error creating outgoing offer: ' . $e->getMessage());
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

            $customer->update($data);

            return $customer;
        } catch (\Exception $e) {
            throw new \Exception('Error updating customer: ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        try {
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
}
