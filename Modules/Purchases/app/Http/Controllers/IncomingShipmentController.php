<?php

namespace Modules\Purchases\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Purchases\app\Services\IncomingShipmentService;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Transformers\IncomingShipmentResource;
use Modules\Customers\app\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\FinancialAccounts\Models\Currency;
use Modules\HumanResources\Models\Employee;

class IncomingShipmentController extends Controller
{

    protected IncomingShipmentService $incomingShipmentService;

    public function __construct(IncomingShipmentService $incomingShipmentService)
    {
        $this->incomingShipmentService = $incomingShipmentService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $offers = $this->incomingShipmentService->index($request);
            return response()->json([
                'success' => true,
                'data' => IncomingShipmentResource::collection($offers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IncomingShipmentRequest $request)
    {
        try {
            $shipment = $this->incomingShipmentService->store($request);
            return response()->json([
                'success' => true,
                'data' => new IncomingShipmentResource($shipment)
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching outgoing offers.'], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show($id, Request $request)
    {
        try {
            $result = $this->incomingShipmentService->show($id, $request);

            return response()->json([
                'success' => true,
                'data' => [
                    'shipment' => new IncomingShipmentResource($result['purchase']),
                    'statistics' => $result['statistics'],
                    'inventory_movements' => $result['inventory_movements'],
                    'formatted_data' => $result['formatted_data']
                ],
                'message' => 'Incoming shipment details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving incoming shipment: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('sales::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(IncomingShipmentRequest $request, $id)
    {
        try {
            $shipment = $this->incomingShipmentService->update($id, $request);

            return response()->json([
                'success' => true,
                'data' => new IncomingShipmentResource($shipment),
                'message' => 'Incoming shipment updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error updating incoming shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id, Request $request)
    {
        try {
            $result = $this->incomingShipmentService->destroy($id, $request);

            return response()->json([
                'success' => true,
                'data' => $result,
                'message' => 'Incoming shipment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error deleting incoming shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted incoming shipment
     */
    public function restore($id, Request $request)
    {
        try {
            $shipment = $this->incomingShipmentService->restore($id, $request);

            return response()->json([
                'success' => true,
                'data' => new IncomingShipmentResource($shipment),
                'message' => 'Incoming shipment restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error restoring incoming shipment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (deleted) incoming shipments
     */
    public function getTrashed(Request $request)
    {
        try {
            $trashedShipments = $this->incomingShipmentService->getTrashed($request);

            return response()->json([
                'success' => true,
                'data' => IncomingShipmentResource::collection($trashedShipments->items()),
                'pagination' => [
                    'current_page' => $trashedShipments->currentPage(),
                    'last_page' => $trashedShipments->lastPage(),
                    'per_page' => $trashedShipments->perPage(),
                    'total' => $trashedShipments->total(),
                    'from' => $trashedShipments->firstItem(),
                    'to' => $trashedShipments->lastItem(),
                ],
                'message' => 'Deleted incoming shipments retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving deleted incoming shipments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get form data for creating/editing incoming shipments
     */
    public function getFormData(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id ?? $request->company_id;

            $data = [
                // Customers dropdown
                'customers' => $this->getCustomersDropdown($companyId),

                // Items dropdown
                'items' => $this->getItemsDropdown($companyId),

                // Units dropdown
                'units' => $this->getUnitsDropdown($companyId),

                // Warehouses dropdown
                'warehouses' => $this->getWarehousesDropdown($companyId),

                // Currencies dropdown
                'currencies' => $this->getCurrenciesDropdown($companyId),

                // Employees dropdown
                'employees' => $this->getEmployeesDropdown($companyId),

                // Status options
                'status_options' => [
                    'draft' => 'Draft',
                    'approved' => 'Approved',
                    'sent' => 'Sent',
                    'invoiced' => 'Invoiced',
                    'cancelled' => 'Cancelled',
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Form data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving form data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search customers for dropdown
     */
    public function searchCustomers(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id ?? $request->company_id;
            $search = $request->get('search', '');

            $customers = Customer::where('company_id', $companyId)
                ->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                          ->orWhere('second_name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%')
                          ->orWhere('mobile', 'like', '%' . $search . '%');
                })
                ->limit(20)
                ->get(['id', 'first_name', 'second_name', 'email', 'mobile'])
                ->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'customer_number' => 'CUST-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                        'name' => trim($customer->first_name . ' ' . $customer->second_name),
                        'email' => $customer->email,
                        'mobile' => $customer->mobile,
                        'label' => trim($customer->first_name . ' ' . $customer->second_name) . ' (' . $customer->email . ')',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers,
                'message' => 'Customers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error searching customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search items for dropdown
     */
    public function searchItems(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id ?? $request->company_id;
            $search = $request->get('search', '');

            $items = Item::where('company_id', $companyId)
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                          ->orWhere('name_ar', 'like', '%' . $search . '%')
                          ->orWhere('item_number', 'like', '%' . $search . '%')
                          ->orWhere('code', 'like', '%' . $search . '%');
                })
                ->with(['unit'])
                ->limit(20)
                ->get(['id', 'item_number', 'code', 'name', 'name_ar', 'unit_id'])
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_number' => $item->item_number ?? $item->code,
                        'name' => $item->name ?? $item->name_ar,
                        'unit_id' => $item->unit_id,
                        'unit_name' => $item->unit->name ?? null,
                        'label' => ($item->item_number ?? $item->code) . ' - ' . ($item->name ?? $item->name_ar),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error searching items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers dropdown data
     */
    private function getCustomersDropdown($companyId)
    {
        return Customer::where('company_id', $companyId)
            ->orderBy('first_name')
            ->limit(100)
            ->get(['id', 'first_name', 'second_name', 'email', 'mobile'])
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'customer_number' => 'CUST-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
                    'name' => trim($customer->first_name . ' ' . $customer->second_name),
                    'email' => $customer->email,
                    'mobile' => $customer->mobile,
                    'label' => trim($customer->first_name . ' ' . $customer->second_name),
                ];
            });
    }

    /**
     * Get items dropdown data
     */
    private function getItemsDropdown($companyId)
    {
        return Item::where('company_id', $companyId)
            ->with(['unit'])
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'item_number', 'code', 'name', 'name_ar', 'unit_id'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number ?? $item->code,
                    'name' => $item->name ?? $item->name_ar,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit->name ?? null,
                    'label' => ($item->item_number ?? $item->code) . ' - ' . ($item->name ?? $item->name_ar),
                ];
            });
    }

    /**
     * Get units dropdown data
     */
    private function getUnitsDropdown($companyId)
    {
        return Unit::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'symbol'])
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'symbol' => $unit->symbol,
                    'label' => $unit->name . ($unit->symbol ? ' (' . $unit->symbol . ')' : ''),
                ];
            });
    }

    /**
     * Get warehouses dropdown data
     */
    private function getWarehousesDropdown($companyId)
    {
        return Warehouse::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'warehouse_number', 'name', 'address'])
            ->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'warehouse_number' => $warehouse->warehouse_number,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'label' => $warehouse->warehouse_number . ' - ' . $warehouse->name,
                ];
            });
    }

    /**
     * Get currencies dropdown data
     */
    private function getCurrenciesDropdown($companyId)
    {
        return Currency::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'symbol'])
            ->map(function ($currency) {
                return [
                    'id' => $currency->id,
                    'name' => $currency->name,
                    'code' => $currency->code,
                    'symbol' => $currency->symbol,
                    'label' => $currency->name . ' (' . $currency->code . ')',
                ];
            });
    }

    /**
     * Get employees dropdown data
     */
    private function getEmployeesDropdown($companyId)
    {
        return Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'employee_number', 'first_name', 'last_name'])
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'employee_number' => $employee->employee_number,
                    'name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'label' => $employee->employee_number . ' - ' . trim($employee->first_name . ' ' . $employee->last_name),
                ];
            });
    }

    /**
     * Advanced search for incoming shipments
     */
    public function search(Request $request)
    {
        try {
            $results = $this->incomingShipmentService->search($request);

            return response()->json([
                'success' => true,
                'data' => IncomingShipmentResource::collection($results['data']),
                'pagination' => $results['pagination'],
                'filters_applied' => $results['filters_applied'],
                'sort' => $results['sort'],
                'message' => 'Search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error searching incoming shipments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sortable fields for incoming shipments
     */
    public function getSortableFields()
    {
        try {
            $sortableFields = [
                'id' => 'ID',
                'invoice_number' => 'Invoice Number',
                'date' => 'Date',
                'time' => 'Time',
                'due_date' => 'Due Date',
                'customer_name' => 'Customer Name',
                'customer_email' => 'Customer Email',
                'customer_mobile' => 'Customer Mobile',
                'licensed_operator' => 'Licensed Operator',
                'total_amount' => 'Total Amount',
                'grand_total' => 'Grand Total',
                'status' => 'Status',
                'ledger_code' => 'Ledger Code',
                'ledger_number' => 'Ledger Number',
                'exchange_rate' => 'Exchange Rate',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At'
            ];

            return response()->json([
                'success' => true,
                'data' => $sortableFields,
                'message' => 'Sortable fields retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving sortable fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sorting options for incoming shipments
     */
    public function getSortingOptions()
    {
        try {
            $sortingOptions = $this->incomingShipmentService->getSortingOptions();

            return response()->json([
                'success' => true,
                'data' => $sortingOptions,
                'message' => 'Sorting options retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving sorting options: ' . $e->getMessage()
            ], 500);
        }
    }
}
