<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\OutgoingOrderService;
use Modules\Purchases\Http\Requests\OutgoingOrderRequest;
use Modules\Purchases\Http\Resources\OutgoingOrderResource;
use Modules\Purchases\Models\Purchase;

class OutgoingOrderController extends Controller
{

    protected OutgoingOrderService $outgoingOrderService;

    public function __construct(OutgoingOrderService $outgoingOrderService)
    {
        $this->outgoingOrderService = $outgoingOrderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $orders = $this->outgoingOrderService->index($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingOrderResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OutgoingOrderRequest $request)
    {
        try {
            $order = $this->outgoingOrderService->store($request);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing order created successfully.',
                'data' => new OutgoingOrderResource($order)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating outgoing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers for dropdown
     */
    public function getCustomers(Request $request)
    {
        try {
            $customers = $this->outgoingOrderService->getCustomers($request);
            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for dropdown
     */
    public function getItems(Request $request)
    {
        try {
            $items = $this->outgoingOrderService->getItems($request);
            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get currencies for dropdown
     */
    public function getCurrencies()
    {
        try {
            $currencies = $this->outgoingOrderService->getCurrencies();
            return response()->json([
                'success' => true,
                'data' => $currencies
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching currencies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tax rates for dropdown
     */
    public function getTaxRates()
    {
        try {
            $taxRates = $this->outgoingOrderService->getTaxRates();
            return response()->json([
                'success' => true,
                'data' => $taxRates
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching tax rates: ' . $e->getMessage()
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
                    'currency_id' => $currencyId,
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'exchange_rate' => $rate,
                    'updated_at' => now()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching exchange rate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating outgoing order
     */
    public function getFormData(Request $request)
    {
        try {
            $data = [
                'customers' => $this->outgoingOrderService->getCustomers(new Request()),
                'currencies' => $this->outgoingOrderService->getCurrencies(),
                'tax_rates' => $this->outgoingOrderService->getTaxRates(),
                'next_order_number' => Purchase::generateOutgoingOrderNumber(),
                'journal_data' => Purchase::generateJournalAndInvoiceNumber($request->user()->company_id ?? 1),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $order = $this->outgoingOrderService->show($id);
            return response()->json([
                'success' => true,
                'data' => new OutgoingOrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching outgoing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OutgoingOrderRequest $request, $id)
    {
        try {
            $order = $this->outgoingOrderService->update($request, $id);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing order updated successfully.',
                'data' => new OutgoingOrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating outgoing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $this->outgoingOrderService->destroy($id, $userId);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing order deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting outgoing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore deleted outgoing order
     */
    public function restore($id)
    {
        try {
            $order = $this->outgoingOrderService->restore($id);
            return response()->json([
                'success' => true,
                'message' => 'Outgoing order restored successfully.',
                'data' => new OutgoingOrderResource($order)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring outgoing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get deleted outgoing orders
     */
    public function getDeleted(Request $request)
    {
        try {
            $orders = $this->outgoingOrderService->getDeleted($request);
            return response()->json([
                'success' => true,
                'data' => OutgoingOrderResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching deleted outgoing orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data
     */
    public function getSearchFormData()
    {
        try {
            $data = $this->outgoingOrderService->getSearchFormData();
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields
     */
    public function getSortableFields()
    {
        try {
            $fields = $this->outgoingOrderService->getSortableFields();
            return response()->json([
                'success' => true,
                'data' => $fields
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching sortable fields: ' . $e->getMessage()
            ], 500);
        }
    }
}
