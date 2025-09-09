<?php

namespace Modules\Suppliers\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Suppliers\app\Services\SupplierService;
use Modules\Suppliers\Http\Requests\SupplierRequest;
use Modules\Suppliers\Models\Supplier;
use Modules\Suppliers\Transformers\SupplierResource;

class SupplierController extends Controller
{

    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }


    public function index(Request $request)
    {

        try {
            $suppliers = $this->supplierService->index($request);
            return response()->json([
                'success' => true,
                'data'    => SupplierResource::collection($suppliers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        try {
            $supplier = $this->supplierService->store($request);
            return response()->json([
                'success' => true,
                'data'    => new SupplierResource($supplier)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->show($supplier);
            return response()->json([
                'success' => true,
                'data'    => new SupplierResource($supplier)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->update($request, $supplier);
            return response()->json([
                'success' => true,
                'data'    => new SupplierResource($supplier)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->destroy($supplier);
            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Supplier $supplier)
    {
        try {
            $supplier = $this->supplierService->restore($supplier);
            return response()->json([
                'success' => true,
                'data'    => new SupplierResource($supplier)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }
}
