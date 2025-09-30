<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Purchases\app\Services\IncomingOfferService;
use Modules\Purchases\Http\Requests\IncomingOfferRequest;
use Modules\Purchases\Http\Requests\IncomingOfferSearchRequest;
use Modules\Purchases\Transformers\IncomingOfferResource;
use Modules\Purchases\Models\Purchase;

/**
 * @group Purchase Management / Incoming Offers
 *
 * APIs for managing incoming purchase offers from suppliers, including offer evaluation and acceptance.
 */
class IncomingOfferController extends Controller
{

    protected IncomingOfferService $incomingOfferService;

    public function __construct(IncomingOfferService $incomingOfferService)
    {
        $this->incomingOfferService = $incomingOfferService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->incomingOfferService->index($request);

            if ($request->get('paginate', true)) {
                return response()->json([
                    'success' => true,
                    'data' => IncomingOfferResource::collection($offers->items()),
                    'meta' => [
                        'current_page' => $offers->currentPage(),
                        'last_page' => $offers->lastPage(),
                        'per_page' => $offers->perPage(),
                        'total' => $offers->total(),
                        'from' => $offers->firstItem(),
                        'to' => $offers->lastItem()
                    ]
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => IncomingOfferResource::collection($offers)
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching incoming quotations.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingOfferRequest $request)
    {
        try {
            $offer = $this->incomingOfferService->store($request);
            return response()->json([
                'success' => true,
                'message' => 'Incoming quotation created successfully',
                'data' => new IncomingOfferResource($offer)
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while creating incoming quotation.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $offer = Purchase::with([
                'user',
                'company',
                'branch',
                'currency',
                'supplier',
                'customer',
                'taxRate',
                'items.item',
                'items.unit',
                'creator',
                'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new IncomingOfferResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Incoming quotation not found.',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomingOfferRequest $request, $id)
    {
        try {
            $offer = $this->incomingOfferService->update($request, $id);

            return response()->json([
                'success' => true,
                'message' => 'Incoming quotation updated successfully',
                'data' => new IncomingOfferResource($offer)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating incoming quotation.',
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
            $offer = Purchase::findOrFail($id);
            $offer->update(['deleted_by' => auth()->id()]);
            $offer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incoming quotation deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting incoming quotation.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing incoming quotations
     */
    public function getFormData(Request $request)
    {
        try {
            $formData = $this->incomingOfferService->getFormData($request);

            return response()->json([
                'success' => true,
                'data' => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items for autocomplete
     */
    public function searchItems(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $items = \Modules\Inventory\Models\Item::forCompany($companyId)
                ->active()
                ->where(function ($query) use ($search) {
                    $query->where('item_number', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%")
                          ->orWhere('name_ar', 'like', "%{$search}%");
                })
                ->with('unit:id,name')
                ->select('id', 'item_number', 'name', 'name_ar', 'unit_id', 'first_sale_price')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_number' => $item->item_number,
                        'name' => $item->name,
                        'name_ar' => $item->name_ar,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit->name ?? null,
                        'first_sale_price' => $item->first_sale_price,
                        'display_name' => $item->item_number . ' - ' . ($item->name_ar ?? $item->name)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching items.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers for autocomplete
     */
    public function searchCustomers(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;
            $search = $request->get('search', '');
            $limit = $request->get('limit', 10);

            $customers = \Modules\Customers\Models\Customer::forCompany($companyId)
                ->active()
                ->where(function ($query) use ($search) {
                    $query->where('customer_number', 'like', "%{$search}%")
                          ->orWhere('customer_name_ar', 'like', "%{$search}%")
                          ->orWhere('customer_name_en', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('mobile', 'like', "%{$search}%");
                })
                ->select('id', 'customer_number', 'customer_name_ar', 'customer_name_en', 'email', 'mobile')
                ->limit($limit)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'customer_number' => $customer->customer_number,
                        'customer_name_ar' => $customer->customer_name_ar,
                        'customer_name_en' => $customer->customer_name_en,
                        'email' => $customer->email,
                        'mobile' => $customer->mobile,
                        'display_name' => $customer->customer_number . ' - ' . ($customer->customer_name_ar ?? $customer->customer_name_en)
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching customers.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get live currency rate
     */
    public function getCurrencyRate(Request $request)
    {
        try {
            $currencyId = $request->get('currency_id');
            $taxRateId = $request->get('tax_rate_id');

            if (!$currencyId) {
                return response()->json([
                    'error' => 'Currency ID is required'
                ], 400);
            }

            // Get currency rate using the service method
            $service = new IncomingOfferService();
            $rateInfo = $service->getCurrencyRate($currencyId, $taxRateId);

            return response()->json([
                'success' => true,
                'data' => $rateInfo
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching currency rate.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advanced search for incoming quotations with multiple criteria
     */
    public function search(IncomingOfferSearchRequest $request)
    {
        try {
            $quotations = $this->incomingOfferService->search($request);

            if ($request->get('paginate', true)) {
                return response()->json([
                    'success' => true,
                    'data' => IncomingOfferResource::collection($quotations->items()),
                    'meta' => [
                        'current_page' => $quotations->currentPage(),
                        'last_page' => $quotations->lastPage(),
                        'per_page' => $quotations->perPage(),
                        'total' => $quotations->total(),
                        'from' => $quotations->firstItem(),
                        'to' => $quotations->lastItem()
                    ],
                    'search_params' => $request->only([
                        'quotation_number', 'quotation_number_from', 'quotation_number_to',
                        'supplier_name', 'date', 'date_from', 'date_to',
                        'amount', 'amount_from', 'amount_to', 'currency_id',
                        'licensed_operator', 'status', 'customer_name',
                        'ledger_code', 'invoice_number', 'sort_by', 'sort_order', 'per_page'
                    ])
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => IncomingOfferResource::collection($quotations)
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while searching incoming quotations.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for incoming quotations
     */
    public function getSortableFields(Request $request)
    {
        try {
            $sortableFields = $this->incomingOfferService->getSortableFields();

            return response()->json([
                'success' => true,
                'data' => $sortableFields
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching sortable fields.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search form data for advanced search
     */
    public function getSearchFormData(Request $request)
    {
        try {
            $companyId = $request->user()->company_id ?? 101;

            $formData = [
                // Status options
                'status_options' => Purchase::STATUS_OPTIONS,

                // Currencies
                'currencies' => \Modules\FinancialAccounts\Models\Currency::where('company_id', $companyId)
                    ->select('id', 'code', 'name', 'symbol')
                    ->get()
                    ->map(function ($currency) {
                        return [
                            'id' => $currency->id,
                            'code' => $currency->code,
                            'name' => $currency->name,
                            'symbol' => $currency->symbol,
                            'display_name' => $currency->code . ' - ' . $currency->name
                        ];
                    }),

                // Suppliers for dropdown
                'suppliers' => \Modules\Suppliers\Models\Supplier::where('company_id', $companyId)
                    ->where('status', 'active')
                    ->select('id', 'supplier_number', 'supplier_name_ar', 'supplier_name_en')
                    ->get()
                    ->map(function ($supplier) {
                        return [
                            'id' => $supplier->id,
                            'supplier_number' => $supplier->supplier_number,
                            'supplier_name_ar' => $supplier->supplier_name_ar,
                            'supplier_name_en' => $supplier->supplier_name_en,
                            'display_name' => $supplier->supplier_number . ' - ' . ($supplier->supplier_name_ar ?? $supplier->supplier_name_en)
                        ];
                    }),

                // Sortable fields
                'sortable_fields' => $this->incomingOfferService->getSortableFields(),
            ];

            return response()->json([
                'success' => true,
                'data' => $formData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
