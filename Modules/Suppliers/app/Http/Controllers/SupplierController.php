<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Suppliers\app\Services\SupplierService;
use Modules\Suppliers\Http\Requests\SupplierRequest;
use Modules\Suppliers\Models\Supplier;
use Modules\Suppliers\Transformers\SupplierResource;

class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function store(SupplierRequest $request)
    {
        try {
            DB::beginTransaction();
            $supplier = $this->supplierService->createSupplier($request->validated(), $request->user());
            DB::commit();
            return new SupplierResource($supplier);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('Failed to create supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $suppliers = $this->supplierService->getSuppliers($request->user());
            return SupplierResource::collection($suppliers);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to retrieve suppliers.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $supplier = $this->supplierService->getSupplierById($id);
            return new SupplierResource($supplier);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to retrieve supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(SupplierRequest $request, $id)
    {
        try {
            DB::beginTransaction();
            $supplier = $this->supplierService->updateSupplier($id, $request->validated(), $request->user());
            DB::commit();
            return new SupplierResource($supplier);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => __('Failed to update supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $this->supplierService->deleteSupplier($id, $user->id);
            return response()->json(['message' => __('Supplier deleted successfully.')]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to delete supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $supplier = $this->supplierService->restoreSupplier($id);
            return new SupplierResource($supplier);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to restore supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:suppliers,id',
        ]);

        try {
            $user = Auth::user();
            $this->supplierService->bulkDelete($request->ids, $user->id);
            return response()->json(['message' => __('Suppliers deleted successfully.')]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to delete suppliers.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
