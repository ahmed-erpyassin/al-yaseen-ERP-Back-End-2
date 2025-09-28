<?php

namespace Modules\Suppliers\app\Services;

use Illuminate\Support\Facades\Auth;
use Modules\Suppliers\Http\Requests\SupplierRequest;
use Modules\Suppliers\Models\Supplier;

class SupplierService
{
    public function createSupplier(array $data, $user)
    {
        $data['user_id'] = $user->id;
        $data['company_id'] = $user->company?->id;
        $data['branch_id'] = $user->branch?->id;
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;

        $supplier = Supplier::create($data);

        return $supplier;
    }

    public function getSuppliers($user)
    {
        return Supplier::with(['user', 'company', 'branch', 'currency', 'employee', 'country', 'region', 'city'])->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->get();
    }

    public function getSupplierById($id)
    {
        $user = Auth::user();
        return Supplier::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
    }

    public function updateSupplier($id, array $data)
    {
        $user = Auth::user();
        $supplier = Supplier::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
        $supplier->update($data);
        return $supplier;
    }

    public function deleteSupplier($id, $userId)
    {
        $user = Auth::user();

        $supplier = Supplier::where('id', $id)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->firstOrFail();
        $supplier->deleted_by = $userId;
        $supplier->save();
        $supplier->delete();
    }

    public function restoreSupplier($id)
    {
        $user = Auth::user();
        $supplier = Supplier::withTrashed()
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->findOrFail($id);
        $supplier->restore();
    }

    public function bulkDelete(array $ids, $userId)
    {
        $user = Auth::user();
        $suppliers = Supplier::whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->where('company_id', $user->company?->id)
            ->get();
        foreach ($suppliers as $supplier) {
            $supplier->deleted_by = $userId;
            $supplier->save();
            $supplier->delete();
        }
    }
}
