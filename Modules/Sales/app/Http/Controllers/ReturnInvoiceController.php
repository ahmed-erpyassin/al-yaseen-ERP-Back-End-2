<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sales\app\Services\ReturnInvoiceService;
use Modules\Sales\Http\Requests\ReturnInvoiceRequest;
use Modules\Sales\Transformers\ReturnInvoiceResource;

/**
 * @group Sales Management / Return Invoices
 *
 * APIs for managing sales return invoices, including return processing, refunds, and inventory adjustments.
 */
class ReturnInvoiceController extends Controller
{
    protected ReturnInvoiceService $returnInvoiceService;

    public function __construct(ReturnInvoiceService $returnInvoiceService)
    {
        $this->returnInvoiceService = $returnInvoiceService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->returnInvoiceService->index($request);
            return response()->json([
                'success' => true,
                'data' => ReturnInvoiceResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReturnInvoiceRequest $request)
    {
        try {
            $invoice = $this->returnInvoiceService->store($request);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($invoice)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $returnInvoice = $this->returnInvoiceService->show($id);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($returnInvoice),
                'message' => 'Return invoice retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReturnInvoiceRequest $request, $id)
    {
        try {
            $returnInvoice = $this->returnInvoiceService->update($request, $id);
            return response()->json([
                'success' => true,
                'data' => new ReturnInvoiceResource($returnInvoice),
                'message' => 'Return invoice updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $result = $this->returnInvoiceService->destroy($id);
            return response()->json([
                'success' => true,
                'message' => 'Return invoice deleted successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        try {
            $customers = $this->returnInvoiceService->searchCustomers($request);
            return response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'Customers retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer by number
     */
    public function getCustomerByNumber(Request $request)
    {
        try {
            $customer = $this->returnInvoiceService->getCustomerByNumber($request);
            return response()->json([
                'success' => true,
                'data' => $customer,
                'message' => 'Customer retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer by name
     */
    public function getCustomerByName(Request $request)
    {
        try {
            $customer = $this->returnInvoiceService->getCustomerByName($request);
            return response()->json([
                'success' => true,
                'data' => $customer,
                'message' => 'Customer retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching customer.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search for items
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
            $currency = \Modules\FinancialAccounts\Models\Currency::find($currencyId);

            if (!$currency) {
                return response()->json([
                    'success' => false,
                    'error' => 'Currency not found.'
                ], 404);
            }

            // Get live rate from external API
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("https://api.exchangerate-api.com/v4/latest/USD");

            $rate = 1; // Default rate
            if ($response->successful()) {
                $rates = $response->json()['rates'] ?? [];
                $rate = $rates[$currency->code] ?? 1;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'currency' => $currency,
                    'exchange_rate' => $rate
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
     * Get form data for return invoice creation
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
     * Advanced search for return invoices
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
                'message' => 'Return invoices retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while searching return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for return invoices
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
     * Get sortable fields for return invoices
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
     * Get deleted return invoices (soft deleted)
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
                'message' => 'Deleted return invoices retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted return invoices.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted return invoice
     */
    public function restore($id)
    {
        try {
            $result = $this->returnInvoiceService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Return invoice restored successfully',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a return invoice (permanent deletion)
     */
    public function forceDelete($id)
    {
        try {
            $result = $this->returnInvoiceService->forceDelete($id);
            return response()->json([
                'success' => true,
                'message' => 'Return invoice permanently deleted',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while permanently deleting return invoice.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
