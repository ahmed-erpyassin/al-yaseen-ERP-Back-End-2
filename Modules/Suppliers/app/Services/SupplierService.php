<?php

namespace Modules\Suppliers\app\Services;

use Modules\Suppliers\Http\Requests\SupplierRequest;
use Modules\Suppliers\Models\Supplier;

class SupplierService
{

    public function index($request)
    {
        try {

            $supplier_search = $request->get('supplier_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Supplier::query()
                ->when($supplier_search, function ($query, $supplier_search) {
                    $query->where('name', 'like', '%' . $supplier_search . '%');
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(SupplierRequest $request)
    {

        try {
            $companyId = $request->user()->company_id;
            $userId = $request->user()->id;

            $data = [
                'company_id' => $companyId,
                'user_id'    => $userId,
                'status'     => 'active',
            ] + $request->validated();

            $supplier = Supplier::create($data);

            return $supplier;
        } catch (\Exception $e) {
            throw new \Exception('Error creating outgoing offer: ' . $e->getMessage());
        }
    }
    public function show(Supplier $supplier)
    {
        try {
            return $supplier;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching supplier: ' . $e->getMessage());
        }
    }
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {

            $data = $request->validated();

            $supplier->update($data);

            return $supplier;
        } catch (\Exception $e) {
            throw new \Exception('Error updating supplier: ' . $e->getMessage());
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting supplier: ' . $e->getMessage());
        }
    }


    public function restore(Supplier $supplier)
    {
        try {
            $supplier->restore();
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error restoring supplier: ' . $e->getMessage());
        }
    }
}
