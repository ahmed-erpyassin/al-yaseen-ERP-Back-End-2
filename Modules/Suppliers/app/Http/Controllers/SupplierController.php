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

/**
 * @group Supplier Management / Suppliers
 *
 * APIs for managing suppliers, including creation, updates, search, and supplier relationship management.
 */
class SupplierController extends Controller
{
    protected $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of suppliers with advanced search and filtering.
     * Supports pagination, sorting, and comprehensive search across multiple fields.
     *
     * @param Request $request Request parameters for filtering and pagination
     * @return JsonResponse Collection of suppliers with pagination metadata
     */
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
     * Store a newly created supplier in storage.
     * Creates a new supplier with comprehensive validation and audit trail.
     *
     * @param SupplierRequest $request Validated supplier data
     * @return SupplierResource|JsonResponse Supplier resource or error response
     */
    public function store(SupplierRequest $request)
    {
        try {
            DB::beginTransaction();
            $supplier = $this->supplierService->store($request);
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
    /**
     * Display the specified supplier with all related data.
     * Returns supplier details with relationships loaded for comprehensive view.
     *
     * @param Supplier $supplier Supplier model instance
     * @return SupplierResource|JsonResponse Supplier resource or error response
     */
    public function show(Supplier $supplier)
    {
        try {
            $supplierData = $this->supplierService->show($supplier);
            return new SupplierResource($supplierData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Failed to retrieve supplier.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified supplier in storage.
     * Updates supplier data with comprehensive validation and relationship handling.
     *
     * @param SupplierRequest $request Validated supplier data
     * @param Supplier $supplier Supplier model instance
     * @return SupplierResource|JsonResponse Updated supplier resource or error response
     */
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

    /**
     * Remove the specified supplier from storage (soft delete).
     * Performs soft delete with audit trail tracking who deleted the supplier.
     *
     * @param Supplier $supplier Supplier model instance
     * @return JsonResponse Success message or error response
     */
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
     * Advanced search for suppliers with multiple criteria.
     * Performs comprehensive search across supplier fields with pagination and sorting.
     *
     * @param Request $request Search criteria and pagination parameters
     * @return JsonResponse Paginated search results with metadata
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
     * Get form data for supplier search interface.
     * Returns dropdown options and filter data for search forms.
     *
     * @return JsonResponse Form data for search interface
     */
    public function getSearchFormData()
    {
        try {
            $formData = $this->supplierService->getSearchFormData();
            return response()->json([
                'success' => true,
                'data'    => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching search form data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get form data for supplier creation and editing.
     * Returns dropdown options and reference data for supplier forms.
     *
     * @return JsonResponse Form data for supplier creation/editing
     */
    public function getFormData()
    {
        try {
            $formData = $this->supplierService->getFormData();
            return response()->json([
                'success' => true,
                'data'    => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching form data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get soft-deleted suppliers with pagination.
     * Returns list of suppliers that have been soft deleted for potential restoration.
     *
     * @param Request $request Request parameters for pagination
     * @return JsonResponse Paginated list of deleted suppliers
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
     * Force delete a supplier (permanent deletion).
     * Permanently removes supplier from database - cannot be undone.
     *
     * @param int $id Supplier ID
     * @return JsonResponse Success message with supplier number or error response
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
     * Get sortable fields for supplier listings.
     * Returns list of fields that can be used for sorting supplier data.
     *
     * @return JsonResponse List of sortable fields with display names
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
            return response()->json([
                'error' => __('Failed to fetch sortable fields.'),
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted supplier.
     * Restores a previously deleted supplier and returns updated resource.
     *
     * @param int $id Supplier ID
     * @return SupplierResource|JsonResponse Restored supplier resource or error response
     */
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
