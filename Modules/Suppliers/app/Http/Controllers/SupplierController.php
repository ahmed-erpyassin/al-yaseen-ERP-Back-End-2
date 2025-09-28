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

            if ($request->get('paginate', true)) {
                return response()->json([
                    'success' => true,
                    'data'    => SupplierResource::collection($suppliers->items()),
                    'meta'    => [
                        'current_page' => $suppliers->currentPage(),
                        'last_page'    => $suppliers->lastPage(),
                        'per_page'     => $suppliers->perPage(),
                        'total'        => $suppliers->total(),
                        'from'         => $suppliers->firstItem(),
                        'to'           => $suppliers->lastItem(),
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'data'    => SupplierResource::collection($suppliers)
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching suppliers: ' . $e->getMessage()], 500);
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
            return response()->json(['error' => 'An error occurred while restoring supplier: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Advanced search for suppliers with multiple criteria
     */
    public function search(Request $request)
    {
        try {
            $suppliers = $this->supplierService->search($request);
            return response()->json([
                'success' => true,
                'data'    => SupplierResource::collection($suppliers->items()),
                'meta'    => [
                    'current_page' => $suppliers->currentPage(),
                    'last_page'    => $suppliers->lastPage(),
                    'per_page'     => $suppliers->perPage(),
                    'total'        => $suppliers->total(),
                    'from'         => $suppliers->firstItem(),
                    'to'           => $suppliers->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while searching suppliers: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get form data for supplier search
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $formData = $this->supplierService->getSearchFormData($request);
            return response()->json([
                'success' => true,
                'data'    => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching search form data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get form data for supplier creation/editing
     */
    public function getFormData(Request $request)
    {
        try {
            $formData = $this->supplierService->getFormData($request);
            return response()->json([
                'success' => true,
                'data'    => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching form data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get deleted suppliers (soft deleted)
     */
    public function getDeleted(Request $request)
    {
        try {
            $suppliers = $this->supplierService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data'    => SupplierResource::collection($suppliers->items()),
                'meta'    => [
                    'current_page' => $suppliers->currentPage(),
                    'last_page'    => $suppliers->lastPage(),
                    'per_page'     => $suppliers->perPage(),
                    'total'        => $suppliers->total(),
                    'from'         => $suppliers->firstItem(),
                    'to'           => $suppliers->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching deleted suppliers: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Force delete a supplier (permanent delete)
     */
    public function forceDelete($id)
    {
        try {
            $result = $this->supplierService->forceDelete($id);
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'supplier_number' => $result['supplier_number']
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while permanently deleting supplier: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get sortable fields for suppliers
     */
    public function getSortableFields(Request $request)
    {
        try {
            $sortableFields = $this->supplierService->getSortableFields();
            return response()->json([
                'success' => true,
                'data'    => $sortableFields
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching sortable fields: ' . $e->getMessage()], 500);
        }
    }
}
