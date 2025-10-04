<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\ReturnInvoiceService;
use Modules\Purchases\app\Http\Requests\ReturnInvoiceRequest;
use Modules\Purchases\Transformers\ReturnInvoiceResource;

/**
 * @group Purchase Management / Return Invoices
 *
 * APIs for managing purchase return invoices, including return processing, refunds, and supplier credits.
 */
class ReturnInvoiceController extends Controller
{
    protected ReturnInvoiceService $returnInvoiceService;

    public function __construct(ReturnInvoiceService $returnInvoiceService)
    {
        $this->returnInvoiceService = $returnInvoiceService;
    }


    /**
     * Display a listing of purchase return invoices with pagination
     */
    public function index(Request $request)
    {
        try {
            $returnInvoices = $this->returnInvoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => ReturnInvoiceResource::collection($returnInvoices->items()),
                'pagination' => [
                    'current_page' => $returnInvoices->currentPage(),
                    'last_page' => $returnInvoices->lastPage(),
                    'per_page' => $returnInvoices->perPage(),
                    'total' => $returnInvoices->total(),
                    'from' => $returnInvoices->firstItem(),
                    'to' => $returnInvoices->lastItem(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching purchase return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created purchase return invoice
     */
    public function store(ReturnInvoiceRequest $request)
    {
        try {
            $invoice = $this->returnInvoiceService->store($request);
            return response()->json([
                'success' => true,
                'message' => 'Purchase return invoice created successfully.',
                'data' => new ReturnInvoiceResource($invoice)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified purchase return invoice with all related data
     */
    public function show($id)
    {
        try {
            $returnInvoice = $this->returnInvoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($returnInvoice),
                'message' => 'Purchase return invoice retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified purchase return invoice
     */
    public function update(ReturnInvoiceRequest $request, $id)
    {
        try {
            $returnInvoice = $this->returnInvoiceService->update($request, $id);
            return response()->json([
                'success' => true,
                'message' => 'Purchase return invoice updated successfully.',
                'data' => new ReturnInvoiceResource($returnInvoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified purchase return invoice
     */
    public function destroy($id)
    {
        try {
            $result = $this->returnInvoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Purchase return invoice deleted successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted purchase return invoice
     */
    public function restore($id)
    {
        try {
            $result = $this->returnInvoiceService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Purchase return invoice restored successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get soft deleted purchase return invoices
     */
    public function getDeleted(Request $request)
    {
        try {
            $returnInvoices = $this->returnInvoiceService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data' => ReturnInvoiceResource::collection($returnInvoices->items()),
                'pagination' => [
                    'current_page' => $returnInvoices->currentPage(),
                    'last_page' => $returnInvoices->lastPage(),
                    'per_page' => $returnInvoices->perPage(),
                    'total' => $returnInvoices->total(),
                    'from' => $returnInvoices->firstItem(),
                    'to' => $returnInvoices->lastItem()
                ],
                'message' => 'Deleted purchase return invoices retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted purchase return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a purchase return invoice (permanent deletion)
     */
    public function forceDelete($id)
    {
        try {
            $result = $this->returnInvoiceService->forceDelete($id);
            return response()->json([
                'success' => true,
                'message' => 'Purchase return invoice permanently deleted',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while permanently deleting purchase return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for suppliers (for dropdown with search)
     */
    public function searchSuppliers(Request $request)
    {
        try {
            $suppliers = $this->returnInvoiceService->searchSuppliers($request);
            return response()->json([
                'success' => true,
                'data' => $suppliers,
                'message' => 'Suppliers retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching suppliers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier by number
     */
    public function getSupplierByNumber(Request $request)
    {
        try {
            $supplier = $this->returnInvoiceService->getSupplierByNumber($request);
            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Supplier retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching supplier.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get supplier by name
     */
    public function getSupplierByName(Request $request)
    {
        try {
            $supplier = $this->returnInvoiceService->getSupplierByName($request);
            return response()->json([
                'success' => true,
                'data' => $supplier,
                'message' => 'Supplier retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching supplier.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for items (for dropdown with search)
     */
    public function searchItems(Request $request)
    {
        try {
            $items = $this->returnInvoiceService->searchItems($request);
            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Items retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching items.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item by number
     */
    public function getItemByNumber(Request $request)
    {
        try {
            $item = $this->returnInvoiceService->getItemByNumber($request);
            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching item.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item by name
     */
    public function getItemByName(Request $request)
    {
        try {
            $item = $this->returnInvoiceService->getItemByName($request);
            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => 'Item retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching item.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live exchange rate for currency
     */
    public function getLiveExchangeRate(Request $request)
    {
        try {
            $currencyId = $request->get('currency_id');
            if (!$currencyId) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency ID is required.'
                ], 400);
            }

            $exchangeRate = $this->returnInvoiceService->getLiveExchangeRate($currencyId);
            return response()->json([
                'success' => true,
                'data' => [
                    'currency_id' => $currencyId,
                    'exchange_rate' => $exchangeRate,
                    'updated_at' => now()
                ],
                'message' => 'Exchange rate retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching exchange rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete form data for purchase return invoice creation
     */
    public function getFormData(Request $request)
    {
        try {
            $formData = $this->returnInvoiceService->getFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => 'Form data retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for purchase return invoices
     */
    public function search(Request $request)
    {
        try {
            $returnInvoices = $this->returnInvoiceService->search($request);
            return response()->json([
                'success' => true,
                'data' => ReturnInvoiceResource::collection($returnInvoices->items()),
                'pagination' => [
                    'current_page' => $returnInvoices->currentPage(),
                    'last_page' => $returnInvoices->lastPage(),
                    'per_page' => $returnInvoices->perPage(),
                    'total' => $returnInvoices->total(),
                    'from' => $returnInvoices->firstItem(),
                    'to' => $returnInvoices->lastItem()
                ],
                'message' => 'Purchase return invoices retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching purchase return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for purchase return invoices
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $formData = $this->returnInvoiceService->getSearchFormData($request);
            return response()->json([
                'success' => true,
                'data' => $formData,
                'message' => 'Search form data retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for purchase return invoices
     */
    public function getSortableFields()
    {
        try {
            $fields = $this->returnInvoiceService->getSortableFields();
            return response()->json([
                'success' => true,
                'data' => $fields,
                'message' => 'Sortable fields retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching sortable fields.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies(Request $request)
    {
        try {
            $currencies = $this->returnInvoiceService->getCurrencies($request);
            return response()->json([
                'success' => true,
                'data' => $currencies,
                'message' => 'Currencies retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching currencies.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax rates for dropdown
     */
    public function getTaxRates(Request $request)
    {
        try {
            $taxRates = $this->returnInvoiceService->getTaxRates($request);
            return response()->json([
                'success' => true,
                'data' => $taxRates,
                'message' => 'Tax rates retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching tax rates.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
