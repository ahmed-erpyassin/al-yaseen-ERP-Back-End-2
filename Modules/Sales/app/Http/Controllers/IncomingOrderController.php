<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Sales\app\Services\IncomingOrderService;
use Modules\Sales\Http\Requests\IncomingOrderRequest;
use Modules\Sales\Transformers\IncomingOrderResource;

/**
 * @group Sales Management / Incoming Orders
 *
 * APIs for managing incoming sales orders, including order processing, tracking, and fulfillment.
 */
class IncomingOrderController extends Controller
{

    protected IncomingOrderService $incomingOrderService;

    public function __construct(IncomingOrderService $incomingOrderService)
    {
        $this->incomingOrderService = $incomingOrderService;
    }

    /**
     * Display a listing of the resource with advanced search and sorting.
     */
    public function index(Request $request)
    {
        try {
            $orders = $this->incomingOrderService->index($request);

            return response()->json([
                'success' => true,
                'data' => IncomingOrderResource::collection($orders->items()),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                ],
                'search_params' => $request->only([
                    'order_number', 'order_number_from', 'order_number_to',
                    'customer_name', 'date', 'date_from', 'date_to',
                    'amount', 'amount_from', 'amount_to', 'currency_id',
                    'licensed_operator', 'status', 'book_code', 'search',
                    'sort_by', 'sort_order', 'per_page'
                ])
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching incoming orders.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingOrderRequest $request)
    {
        try {
            $order = $this->incomingOrderService->store($request);
            return response()->json([
                'success' => true,
                'data' => new IncomingOrderResource($order),
                'message' => 'Incoming order created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while creating incoming order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating incoming orders
     */
    public function getFormData()
    {
        try {
            $user = Auth::user();

            $data = [
                'currencies' => \Modules\FinancialAccounts\Models\Currency::select('id', 'name', 'code', 'symbol')
                    ->get(),

                'employees' => \Modules\HumanResources\Models\Employee::select('id', 'employee_number', 'first_name', 'second_name')
                    ->get()
                    ->map(function ($employee) {
                        return [
                            'id' => $employee->id,
                            'employee_number' => $employee->employee_number,
                            'full_name' => trim($employee->first_name . ' ' . $employee->second_name),
                            'display_name' => $employee->employee_number . ' - ' . trim($employee->first_name . ' ' . $employee->second_name)
                        ];
                    }),

                'branches' => \Modules\Companies\Models\Branch::select('id', 'name', 'code')
                    ->get(),

                'customers' => \Modules\Customers\Models\Customer::select('id', 'customer_number', 'company_name', 'first_name', 'second_name', 'email')
                    ->get()
                    ->map(function ($customer) {
                        return [
                            'id' => $customer->id,
                            'customer_number' => $customer->customer_number,
                            'company_name' => $customer->company_name,
                            'full_name' => trim($customer->first_name . ' ' . $customer->second_name),
                            'display_name' => $customer->customer_number . ' - ' . $customer->company_name,
                            'email' => $customer->email
                        ];
                    }),

                'items' => \Modules\Inventory\Models\Item::select('id', 'item_number', 'name', 'first_sale_price', 'unit_id')
                    ->where('active', true)
                    ->with('unit:id,name')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_number' => $item->item_number,
                            'name' => $item->name,
                            'first_sale_price' => $item->first_sale_price,
                            'unit_name' => $item->unit ? $item->unit->name : null,
                            'display_name' => $item->item_number . ' - ' . $item->name
                        ];
                    }),

                'tax_rates' => \Modules\FinancialAccounts\Models\TaxRate::select('id', 'name', 'rate')
                    ->get(),

                'company_vat_rate' => $user->company ? $user->company->vat_rate : 0,

                'next_book_code' => \Modules\Sales\Models\Sale::generateBookCode(),
                'next_invoice_number' => \Modules\Sales\Models\Sale::generateInvoiceNumber(),
            ];

            return response()->json([
                'success' => true,
                'data' => $data
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
     * Show the specified resource.
     */
    public function show($id)
    {
        try {
            $order = $this->incomingOrderService->show($id);

            return response()->json([
                'success' => true,
                'data' => new IncomingOrderResource($order),
                'message' => 'Incoming order retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching incoming order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers by name or number
     */
    public function searchCustomers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $customers = \Modules\Customers\Models\Customer::select('id', 'customer_number', 'company_name', 'first_name', 'second_name', 'email')
                ->where(function ($query) use ($search) {
                    $query->where('customer_number', 'like', '%' . $search . '%')
                          ->orWhere('company_name', 'like', '%' . $search . '%')
                          ->orWhere('first_name', 'like', '%' . $search . '%')
                          ->orWhere('second_name', 'like', '%' . $search . '%');
                })
                ->limit(20)
                ->get()
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'customer_number' => $customer->customer_number,
                        'company_name' => $customer->company_name,
                        'full_name' => trim($customer->first_name . ' ' . $customer->second_name),
                        'display_name' => $customer->customer_number . ' - ' . $customer->company_name,
                        'email' => $customer->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
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
     * Search items by name or number
     */
    public function searchItems(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $items = \Modules\Inventory\Models\Item::select('id', 'item_number', 'name', 'first_sale_price', 'unit_id')
                ->where('active', true)
                ->where(function ($query) use ($search) {
                    $query->where('item_number', 'like', '%' . $search . '%')
                          ->orWhere('name', 'like', '%' . $search . '%');
                })
                ->with('unit:id,name')
                ->limit(20)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_number' => $item->item_number,
                        'name' => $item->name,
                        'first_sale_price' => $item->first_sale_price,
                        'unit_name' => $item->unit ? $item->unit->name : null,
                        'display_name' => $item->item_number . ' - ' . $item->name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items
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
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'exchange_rate' => $rate
                ]
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
     * Update the specified resource in storage.
     */
    public function update(IncomingOrderRequest $request, $id)
    {
        try {
            $order = $this->incomingOrderService->update($request, $id);

            return response()->json([
                'success' => true,
                'data' => new IncomingOrderResource($order),
                'message' => 'Incoming order updated successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while updating incoming order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (soft delete).
     */
    public function destroy($id)
    {
        try {
            $this->incomingOrderService->destroy($id);

            return response()->json([
                'success' => true,
                'message' => 'Incoming order deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while deleting incoming order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft deleted incoming order.
     */
    public function restore($id)
    {
        try {
            $order = $this->incomingOrderService->restore($id);

            return response()->json([
                'success' => true,
                'data' => new IncomingOrderResource($order),
                'message' => 'Incoming order restored successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while restoring incoming order.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get advanced search form data
     */
    public function getSearchFormData()
    {
        try {
            $data = [
                'currencies' => \Modules\FinancialAccounts\Models\Currency::select('id', 'name', 'code', 'symbol')
                    ->get(),

                'customers' => \Modules\Customers\Models\Customer::select('id', 'customer_number', 'company_name', 'first_name', 'second_name')
                    ->get()
                    ->map(function ($customer) {
                        return [
                            'id' => $customer->id,
                            'customer_number' => $customer->customer_number,
                            'display_name' => $customer->customer_number . ' - ' . $customer->company_name
                        ];
                    }),

                'status_options' => [
                    'draft' => 'Draft',
                    'approved' => 'Approved',
                    'sent' => 'Sent',
                    'invoiced' => 'Invoiced',
                    'cancelled' => 'Cancelled'
                ],

                'sort_options' => [
                    'id' => 'Order ID',
                    'invoice_number' => 'Invoice Number',
                    'book_code' => 'Book Code',
                    'date' => 'Date',
                    'due_date' => 'Due Date',
                    'total_amount' => 'Total Amount',
                    'status' => 'Status',
                    'created_at' => 'Created Date',
                    'updated_at' => 'Updated Date'
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while fetching search form data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
