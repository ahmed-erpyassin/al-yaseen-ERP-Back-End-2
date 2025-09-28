<?php

namespace Modules\Customers\app\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customers\Http\Requests\CustomerRequest;
use Modules\Customers\Models\Customer;

class CustomerService
{
    public function createCustomer(array $data, $user)
    {
        $data['user_id'] = $user->id;
        $data['company_id'] = $user->company?->id;
        $data['branch_id'] = $user->branch?->id;
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        $customer = Customer::create($data);

        return $customer;
    }

    public function getCustomers($user)
    {
        return Customer::where('user_id', $user->id)->where('company_id', $user->company?->id)->get();
    }

    public function getCustomerById($id)
    {
        $user = Auth::user();
        return Customer::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
    }

    public function updateCustomer($id, array $data)
    {
        $user = Auth::user();
        $customer = Customer::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
        $customer->update($data);
        return $customer;
    }

    public function deleteCustomer($id, $userId)
    {
        $user = Auth::user();

        $customer = Customer::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
        $customer->deleted_by = $userId;
        $customer->save();
        $customer->delete();
    }

    public function restoreCustomer($id)
    {
        $user = Auth::user();
        $customer = Customer::withTrashed()->where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
        $customer->restore();
    }

    public function bulkDelete(array $ids, $userId)
    {
        $user = Auth::user();
        $customers = Customer::whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->get();
        foreach ($customers as $customer) {
            $customer->deleted_by = $userId;
            $customer->save();
            $customer->delete();
        }
    }
}
