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
/*
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
*/
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

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        try {
            $updatedSupplier = $this->supplierService->update($request, $supplier);
            return new SupplierResource($updatedSupplier);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to update supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            $this->supplierService->destroy($supplier);
            return response()->json(['message' => __('Supplier deleted successfully.')]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting supplier: ' . $e->getMessage()], 500);
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
    public function getSortableFields()
    {
        try {
            $sortableFields = $this->supplierService->getSortableFields();
            return response()->json([
                'success' => true,
                'data'    => $sortableFields
            ], 200);
        } catch (\Exception $e) {
         //   return response()->json(['error' => 'An error occurred while fetching sortable fields: ' . $e->getMessage()], 500);

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


}
