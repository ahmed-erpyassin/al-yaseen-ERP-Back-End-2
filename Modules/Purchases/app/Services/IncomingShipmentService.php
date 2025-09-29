<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Models\Purchase;
use Modules\Purchases\Models\PurchaseItem;
use Modules\Customers\app\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryMovementData;

class IncomingShipmentService
{
    public function index(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = Purchase::query()
                ->where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->with([
                    'customer:id,first_name,second_name,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email',
                    'items:id,purchase_id,item_id,item_name,quantity,unit_price,total',
                    'items.item:id,name,item_number',
                    'items.warehouse:id,name,warehouse_number'
                ]);

            // Apply search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $sortBy, $sortOrder);

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching incoming shipments: ' . $e->getMessage());
        }
    }

    public function store(IncomingShipmentRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;
                $branchId = $request->user()->branch_id ?? $request->branch_id;

                // Generate automatic fields
                $autoFields = $this->generateAutoFields($companyId, PurchaseTypeEnum::INCOMING_SHIPMENT);

                // Get customer details if not provided
                $customerData = $this->getCustomerData($request->customer_id);

                // Prepare purchase data
                $purchaseData = [
                    'type' => PurchaseTypeEnum::INCOMING_SHIPMENT,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'branch_id' => $branchId,
                    'status' => 'draft',

                    // Auto-generated fields
                    'ledger_code' => $autoFields['ledger_code'],
                    'ledger_number' => $autoFields['ledger_number'],
                    'ledger_invoice_count' => $autoFields['ledger_invoice_count'],
                    'invoice_number' => $autoFields['invoice_number'],
                    'date' => $autoFields['date'],
                    'time' => $autoFields['time'],

                    // Customer data
                    'customer_number' => $customerData['customer_number'] ?? null,
                    'customer_name' => $customerData['customer_name'] ?? null,
                    'customer_email' => $request->customer_email ?? $customerData['email'],
                    'customer_mobile' => $request->customer_mobile ?? $customerData['mobile'],
                ] + $request->validated();

                // Create the purchase
                $purchase = Purchase::create($purchaseData);

                // Create purchase items with auto-generated serial numbers
                if ($request->has('items') && is_array($request->items)) {
                    $this->createPurchaseItems($purchase, $request->items, $autoFields['shipment_number']);
                }

                // Update inventory (increment warehouse stock)
                $this->updateInventory($purchase);

                return $purchase->load(['items', 'customer', 'currency', 'employee']);
            });
        } catch (Exception $e) {
            throw new Exception('Error creating incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Generate automatic fields for incoming shipment
     */
    private function generateAutoFields($companyId, $type)
    {
        // Generate ledger information
        $ledgerInfo = Purchase::generateLedgerInfo($companyId, $type);

        // Generate sequential invoice number
        $invoiceNumber = $this->generateInvoiceNumber($companyId, $type, $ledgerInfo['ledger_invoice_count']);

        // Generate shipment number
        $shipmentNumber = $this->generateShipmentNumber($companyId);

        return [
            'ledger_code' => $ledgerInfo['ledger_code'],
            'ledger_number' => $ledgerInfo['ledger_number'],
            'ledger_invoice_count' => $ledgerInfo['ledger_invoice_count'],
            'invoice_number' => $invoiceNumber,
            'shipment_number' => $shipmentNumber,
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString(),
        ];
    }

    /**
     * Generate sequential invoice number
     */
    private function generateInvoiceNumber($companyId, $type, $ledgerInvoiceCount)
    {
        // Get the total count of invoices across all ledgers for this type
        $totalInvoiceCount = Purchase::where('company_id', $companyId)
            ->where('type', $type)
            ->whereNotNull('invoice_number')
            ->count() + 1;

        return 'INV-' . str_pad($totalInvoiceCount, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate shipment number
     */
    private function generateShipmentNumber($companyId)
    {
        $count = Purchase::where('company_id', $companyId)
            ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
            ->count() + 1;

        return 'SHIP-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get customer data
     */
    private function getCustomerData($customerId)
    {
        if (!$customerId) {
            return [];
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return [];
        }

        return [
            'customer_number' => $customer->customer_number ?? 'CUST-' . str_pad($customer->id, 4, '0', STR_PAD_LEFT),
            'customer_name' => trim(($customer->first_name ?? '') . ' ' . ($customer->second_name ?? '')),
            'email' => $customer->email,
            'mobile' => $customer->mobile,
        ];
    }

    /**
     * Create purchase items with auto-generated data
     */
    private function createPurchaseItems($purchase, $items, $shipmentNumber)
    {
        foreach ($items as $index => $itemData) {
            // Get item details
            $item = Item::find($itemData['item_id']);
            $unit = Unit::find($itemData['unit_id'] ?? null);
            $warehouse = Warehouse::find($itemData['warehouse_id'] ?? null);

            // Prepare item data
            $purchaseItemData = [
                'purchase_id' => $purchase->id,
                'serial_number' => $index + 1,
                'shipment_number' => $shipmentNumber,
                'item_id' => $itemData['item_id'],
                'item_number' => $item->item_number ?? $item->code ?? null,
                'item_name' => $item->name ?? $item->name_ar ?? null,
                'unit_id' => $itemData['unit_id'] ?? $item->unit_id ?? null,
                'unit_name' => $unit->name ?? null,
                'warehouse_id' => $itemData['warehouse_id'] ?? null,
                'warehouse_number' => $warehouse->warehouse_number ?? null,
                'description' => $itemData['description'] ?? null,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'],
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                'discount_amount' => $itemData['discount_amount'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'total_foreign' => $itemData['total_foreign'] ?? 0,
                'total_local' => $itemData['total_local'] ?? 0,
                'total' => $itemData['total'] ?? ($itemData['quantity'] * $itemData['unit_price']),
                'notes' => $itemData['notes'] ?? null,
            ];

            PurchaseItem::create($purchaseItemData);
        }
    }

    /**
     * Update inventory - increment warehouse stock for incoming shipments
     */
    private function updateInventory($purchase)
    {
        // Create inventory movement header
        $movementData = [
            'company_id' => $purchase->company_id,
            'user_id' => $purchase->user_id,
            'movement_number' => $this->generateMovementNumber($purchase->company_id),
            'movement_type' => 'inbound',
            'movement_date' => Carbon::now()->toDateString(),
            'movement_time' => Carbon::now()->toTimeString(),
            'movement_datetime' => Carbon::now(),
            'customer_id' => $purchase->customer_id,
            'customer_name' => $purchase->customer_name,
            'movement_description' => 'Incoming Shipment - ' . $purchase->invoice_number,
            'inbound_invoice_id' => $purchase->id,
            'inbound_invoice_number' => $purchase->invoice_number,
            'shipment_number' => $purchase->items->first()->shipment_number ?? null,
            'invoice_number' => $purchase->invoice_number,
            'reference' => 'Purchase ID: ' . $purchase->id,
            'status' => 'confirmed',
            'is_confirmed' => true,
            'confirmed_at' => Carbon::now(),
            'confirmed_by' => $purchase->user_id,
            'created_by' => $purchase->user_id,
        ];

        // Calculate totals
        $totalQuantity = $purchase->items->sum('quantity');
        $totalValue = $purchase->items->sum('total');
        $totalItems = $purchase->items->count();

        $movementData['total_quantity'] = $totalQuantity;
        $movementData['total_value'] = $totalValue;
        $movementData['total_items'] = $totalItems;

        $inventoryMovement = InventoryMovement::create($movementData);

        // Create inventory movement data for each item
        foreach ($purchase->items as $item) {
            if ($item->warehouse_id && $item->item_id && $item->quantity > 0) {
                // Get current item stock
                $inventoryItem = Item::find($item->item_id);
                $previousQuantity = $inventoryItem->balance ?? 0;

                $movementItemData = [
                    'company_id' => $purchase->company_id,
                    'inventory_movement_id' => $inventoryMovement->id,
                    'item_id' => $item->item_id,
                    'unit_id' => $item->unit_id,
                    'warehouse_id' => $item->warehouse_id,
                    'quantity' => $item->quantity,
                    'previous_quantity' => $previousQuantity,
                    'new_quantity' => $previousQuantity + $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'unit_price' => $item->unit_price,
                    'total_cost' => $item->total,
                    'total_price' => $item->total,
                    'notes' => $item->notes ?? 'Incoming shipment item',
                    'serial_number' => $item->serial_number,
                    'created_by' => $purchase->user_id,
                ];

                InventoryMovementData::create($movementItemData);

                // Update item balance
                if ($inventoryItem) {
                    $inventoryItem->balance = $previousQuantity + $item->quantity;
                    $inventoryItem->updated_by = $purchase->user_id;
                    $inventoryItem->save();
                }
            }
        }

        return $inventoryMovement;
    }

    /**
     * Generate movement number
     */
    private function generateMovementNumber($companyId)
    {
        $count = InventoryMovement::where('company_id', $companyId)
            ->where('movement_type', 'inbound')
            ->count() + 1;

        return 'MOV-IN-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Apply search filters to the query
     */
    private function applySearchFilters($query, $request)
    {
        // Shipment number search (from/to)
        if ($request->filled('shipment_number_from')) {
            $query->where('invoice_number', '>=', $request->shipment_number_from);
        }

        if ($request->filled('shipment_number_to')) {
            $query->where('invoice_number', '<=', $request->shipment_number_to);
        }

        // Customer name search
        if ($request->filled('customer_name')) {
            $customerName = $request->customer_name;
            $query->where(function ($q) use ($customerName) {
                $q->where('customer_name', 'like', '%' . $customerName . '%')
                  ->orWhereHas('customer', function ($customerQuery) use ($customerName) {
                      $customerQuery->where('first_name', 'like', '%' . $customerName . '%')
                                   ->orWhere('second_name', 'like', '%' . $customerName . '%');
                  });
            });
        }

        // Exact date search
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Amount search (from/to)
        if ($request->filled('amount_from')) {
            $query->where('total_amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('total_amount', '<=', $request->amount_to);
        }

        // Currency search
        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        // Licensed operator search
        if ($request->filled('licensed_operator')) {
            $query->where('licensed_operator', 'like', '%' . $request->licensed_operator . '%');
        }

        // Status search
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range search
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting($query, $sortBy, $sortOrder)
    {
        $allowedSortFields = [
            'id', 'invoice_number', 'date', 'time', 'due_date', 'customer_name',
            'customer_email', 'customer_mobile', 'licensed_operator', 'total_amount',
            'grand_total', 'status', 'ledger_code', 'ledger_number', 'created_at',
            'updated_at', 'exchange_rate', 'currency_id', 'employee_id'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Advanced search for incoming shipments
     */
    public function search(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $perPage = $request->get('per_page', 15);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = Purchase::query()
                ->where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->with([
                    'customer:id,first_name,second_name,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email',
                    'items:id,purchase_id,item_id,item_name,quantity,unit_price,total',
                    'items.item:id,name,item_number',
                    'items.warehouse:id,name,warehouse_number'
                ]);

            // Apply all search filters
            $this->applySearchFilters($query, $request);

            // Apply sorting
            $this->applySorting($query, $sortBy, $sortOrder);

            $results = $query->paginate($perPage);

            return [
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                    'from' => $results->firstItem(),
                    'to' => $results->lastItem(),
                ],
                'filters_applied' => $this->getAppliedFilters($request),
                'sort' => [
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error searching incoming shipments: ' . $e->getMessage());
        }
    }

    /**
     * Get applied filters for response
     */
    private function getAppliedFilters($request)
    {
        return [
            'shipment_number_from' => $request->get('shipment_number_from'),
            'shipment_number_to' => $request->get('shipment_number_to'),
            'customer_name' => $request->get('customer_name'),
            'date' => $request->get('date'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'amount_from' => $request->get('amount_from'),
            'amount_to' => $request->get('amount_to'),
            'currency_id' => $request->get('currency_id'),
            'licensed_operator' => $request->get('licensed_operator'),
            'status' => $request->get('status'),
        ];
    }

    /**
     * Update an incoming shipment
     */
    public function update($id, IncomingShipmentRequest $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;

                // Find the existing shipment
                $purchase = Purchase::where('company_id', $companyId)
                    ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                    ->with(['items'])
                    ->findOrFail($id);

                // Store original data for inventory reversal
                $originalItems = $purchase->items->toArray();

                // Get customer details if customer changed
                $customerData = $this->getCustomerData($request->customer_id);

                // Prepare update data
                $updateData = [
                    'customer_id' => $request->customer_id,
                    'currency_id' => $request->currency_id,
                    'employee_id' => $request->employee_id,
                    'branch_id' => $request->branch_id ?? $purchase->branch_id,
                    'due_date' => $request->due_date,
                    'customer_email' => $request->customer_email ?? $customerData['email'],
                    'customer_mobile' => $request->customer_mobile ?? $customerData['mobile'],
                    'licensed_operator' => $request->licensed_operator,
                    'customer_number' => $customerData['customer_number'] ?? $purchase->customer_number,
                    'customer_name' => $customerData['customer_name'] ?? $purchase->customer_name,
                    'cash_paid' => $request->cash_paid ?? 0,
                    'checks_paid' => $request->checks_paid ?? 0,
                    'allowed_discount' => $request->allowed_discount ?? 0,
                    'discount_percentage' => $request->discount_percentage ?? 0,
                    'discount_amount' => $request->discount_amount ?? 0,
                    'total_without_tax' => $request->total_without_tax ?? 0,
                    'tax_percentage' => $request->tax_percentage ?? 0,
                    'tax_amount' => $request->tax_amount ?? 0,
                    'total_amount' => $request->total_amount ?? 0,
                    'grand_total' => $request->grand_total ?? 0,
                    'remaining_balance' => $request->remaining_balance ?? 0,
                    'exchange_rate' => $request->exchange_rate,
                    'total_foreign' => $request->total_foreign ?? 0,
                    'total_local' => $request->total_local ?? 0,
                    'notes' => $request->notes,
                    'updated_by' => $userId,
                ];

                // Update the purchase
                $purchase->update($updateData);

                // Handle items update if provided
                if ($request->has('items') && is_array($request->items)) {
                    // First, reverse inventory for existing items
                    $this->reverseInventoryForShipment($purchase, $originalItems);

                    // Delete existing items (soft delete)
                    $purchase->items()->delete();

                    // Create new items and update inventory
                    $this->createPurchaseItems($purchase, $request->items, $purchase->items->first()->shipment_number ?? $this->generateShipmentNumber($companyId));

                    // Update inventory with new items
                    $this->updateInventory($purchase->fresh(['items']));
                }

                // Reload with all relationships
                return $purchase->load([
                    'customer:id,first_name,second_name,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email',
                    'items:id,purchase_id,item_id,item_name,quantity,unit_price,total,warehouse_id',
                    'items.item:id,name,item_number',
                    'items.warehouse:id,name,warehouse_number',
                    'items.unit:id,name,symbol'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Reverse inventory for shipment items
     */
    private function reverseInventoryForShipment($purchase, $originalItems)
    {
        foreach ($originalItems as $itemData) {
            if ($itemData['warehouse_id'] && $itemData['item_id'] && $itemData['quantity'] > 0) {
                // Get current item stock
                $inventoryItem = Item::find($itemData['item_id']);

                if ($inventoryItem && $inventoryItem->balance >= $itemData['quantity']) {
                    // Subtract the quantity that was previously added
                    $inventoryItem->balance -= $itemData['quantity'];
                    $inventoryItem->updated_by = $purchase->user_id;
                    $inventoryItem->save();

                    // Create reverse inventory movement record
                    $movementData = [
                        'company_id' => $purchase->company_id,
                        'user_id' => $purchase->user_id,
                        'movement_number' => $this->generateMovementNumber($purchase->company_id) . '-REV',
                        'movement_type' => 'outbound',
                        'movement_date' => Carbon::now()->toDateString(),
                        'movement_time' => Carbon::now()->toTimeString(),
                        'movement_datetime' => Carbon::now(),
                        'customer_id' => $purchase->customer_id,
                        'customer_name' => $purchase->customer_name,
                        'movement_description' => 'Reverse Incoming Shipment - ' . $purchase->invoice_number,
                        'inbound_invoice_id' => $purchase->id,
                        'inbound_invoice_number' => $purchase->invoice_number,
                        'reference' => 'Reverse Purchase ID: ' . $purchase->id,
                        'status' => 'confirmed',
                        'is_confirmed' => true,
                        'confirmed_at' => Carbon::now(),
                        'confirmed_by' => $purchase->user_id,
                        'created_by' => $purchase->user_id,
                        'total_quantity' => $itemData['quantity'],
                        'total_value' => $itemData['total'],
                        'total_items' => 1,
                    ];

                    $inventoryMovement = InventoryMovement::create($movementData);

                    // Create movement data
                    InventoryMovementData::create([
                        'company_id' => $purchase->company_id,
                        'inventory_movement_id' => $inventoryMovement->id,
                        'item_id' => $itemData['item_id'],
                        'unit_id' => $itemData['unit_id'],
                        'warehouse_id' => $itemData['warehouse_id'],
                        'quantity' => -$itemData['quantity'], // Negative for reversal
                        'previous_quantity' => $inventoryItem->balance + $itemData['quantity'],
                        'new_quantity' => $inventoryItem->balance,
                        'unit_cost' => $itemData['unit_price'],
                        'unit_price' => $itemData['unit_price'],
                        'total_cost' => $itemData['total'],
                        'total_price' => $itemData['total'],
                        'notes' => 'Reverse incoming shipment item - Update operation',
                        'created_by' => $purchase->user_id,
                    ]);
                }
            }
        }
    }

    /**
     * Show a specific incoming shipment with all details
     */
    public function show($id, Request $request)
    {
        try {
            $companyId = $request->user()->company_id;

            $purchase = Purchase::where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->with([
                    'customer:id,first_name,second_name,email,mobile,customer_number,address,city,country',
                    'currency:id,name,code,symbol,exchange_rate',
                    'employee:id,first_name,last_name,employee_number,email,mobile',
                    'user:id,first_name,second_name,email',
                    'branch:id,name,address,phone,email',
                    'items:id,purchase_id,item_id,item_name,item_number,quantity,unit_id,unit_price,total,warehouse_id,shipment_number,warehouse_number,notes,serial_number',
                    'items.item:id,name,item_number,description,barcode,category_id',
                    'items.item.category:id,name',
                    'items.warehouse:id,name,warehouse_number,address,phone',
                    'items.unit:id,name,symbol,code'
                ])
                ->findOrFail($id);

            // Calculate additional statistics
            $statistics = [
                'total_items' => $purchase->items->count(),
                'total_quantity' => $purchase->items->sum('quantity'),
                'average_unit_price' => $purchase->items->avg('unit_price'),
                'highest_unit_price' => $purchase->items->max('unit_price'),
                'lowest_unit_price' => $purchase->items->min('unit_price'),
                'unique_warehouses' => $purchase->items->pluck('warehouse_id')->unique()->count(),
                'unique_items' => $purchase->items->pluck('item_id')->unique()->count(),
            ];

            // Get related inventory movements
            $inventoryMovements = InventoryMovement::where('inbound_invoice_id', $purchase->id)
                ->with(['movementData.item:id,name,item_number', 'movementData.warehouse:id,name,warehouse_number'])
                ->get();

            return [
                'purchase' => $purchase,
                'statistics' => $statistics,
                'inventory_movements' => $inventoryMovements,
                'formatted_data' => $this->formatPurchaseData($purchase)
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error retrieving incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Format purchase data for display
     */
    private function formatPurchaseData($purchase)
    {
        return [
            'header_info' => [
                'invoice_number' => $purchase->invoice_number,
                'ledger_code' => $purchase->ledger_code,
                'ledger_number' => $purchase->ledger_number,
                'date' => $purchase->date,
                'time' => $purchase->time,
                'due_date' => $purchase->due_date,
                'status' => $purchase->status,
            ],
            'customer_info' => [
                'customer_number' => $purchase->customer_number,
                'customer_name' => $purchase->customer_name,
                'customer_email' => $purchase->customer_email,
                'customer_mobile' => $purchase->customer_mobile,
                'full_customer_data' => $purchase->customer,
            ],
            'financial_info' => [
                'currency' => $purchase->currency,
                'exchange_rate' => $purchase->exchange_rate,
                'total_without_tax' => $purchase->total_without_tax,
                'tax_percentage' => $purchase->tax_percentage,
                'tax_amount' => $purchase->tax_amount,
                'discount_percentage' => $purchase->discount_percentage,
                'discount_amount' => $purchase->discount_amount,
                'total_amount' => $purchase->total_amount,
                'grand_total' => $purchase->grand_total,
                'cash_paid' => $purchase->cash_paid,
                'checks_paid' => $purchase->checks_paid,
                'remaining_balance' => $purchase->remaining_balance,
                'total_foreign' => $purchase->total_foreign,
                'total_local' => $purchase->total_local,
            ],
            'operational_info' => [
                'licensed_operator' => $purchase->licensed_operator,
                'employee' => $purchase->employee,
                'user' => $purchase->user,
                'branch' => $purchase->branch,
                'notes' => $purchase->notes,
            ],
            'items_summary' => $purchase->items->map(function ($item) {
                return [
                    'serial_number' => $item->serial_number,
                    'shipment_number' => $item->shipment_number,
                    'item_number' => $item->item_number,
                    'item_name' => $item->item_name,
                    'item_details' => $item->item,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => $item->unit_price,
                    'total' => $item->total,
                    'warehouse' => $item->warehouse,
                    'warehouse_number' => $item->warehouse_number,
                    'notes' => $item->notes,
                ];
            }),
            'audit_info' => [
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->updated_at,
                'created_by' => $purchase->created_by,
                'updated_by' => $purchase->updated_by,
            ]
        ];
    }

    /**
     * Soft delete an incoming shipment
     */
    public function destroy($id, Request $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;

                $purchase = Purchase::where('company_id', $companyId)
                    ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                    ->with(['items'])
                    ->findOrFail($id);

                // Store original items data for inventory reversal
                $originalItems = $purchase->items->toArray();

                // Reverse inventory for all items
                $this->reverseInventoryForShipment($purchase, $originalItems);

                // Soft delete the purchase items first
                $purchase->items()->update(['deleted_by' => $userId]);
                $purchase->items()->delete();

                // Soft delete the purchase
                $purchase->update([
                    'deleted_by' => $userId,
                    'status' => 'cancelled'
                ]);
                $purchase->delete();

                return [
                    'message' => 'Incoming shipment deleted successfully',
                    'deleted_items_count' => count($originalItems),
                    'inventory_reversed' => true
                ];
            });
        } catch (\Exception $e) {
            throw new \Exception('Error deleting incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted incoming shipment
     */
    public function restore($id, Request $request)
    {
        try {
            return DB::transaction(function () use ($id, $request) {
                $companyId = $request->user()->company_id;
                $userId = $request->user()->id;

                $purchase = Purchase::withTrashed()
                    ->where('company_id', $companyId)
                    ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                    ->with(['items' => function($query) {
                        $query->withTrashed();
                    }])
                    ->findOrFail($id);

                if (!$purchase->trashed()) {
                    throw new \Exception('Incoming shipment is not deleted');
                }

                // Check inventory availability before restoring
                foreach ($purchase->items as $item) {
                    $this->validateInventoryForRestore($item);
                }

                // Restore the purchase
                $purchase->restore();
                $purchase->update([
                    'status' => 'draft',
                    'deleted_by' => null,
                    'updated_by' => $userId
                ]);

                // Restore items
                $purchase->items()->withTrashed()->update([
                    'deleted_by' => null,
                    'deleted_at' => null
                ]);

                // Re-add inventory
                $this->updateInventory($purchase->fresh(['items']));

                return $purchase->load([
                    'customer:id,first_name,second_name,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email',
                    'items:id,purchase_id,item_id,item_name,quantity,unit_price,total,warehouse_id',
                    'items.item:id,name,item_number',
                    'items.warehouse:id,name,warehouse_number',
                    'items.unit:id,name,symbol'
                ]);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error restoring incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Validate inventory availability for restore
     */
    private function validateInventoryForRestore($item)
    {
        // For incoming shipments, we're adding inventory back, so no validation needed
        // This method is here for consistency with the pattern
        return true;
    }

    /**
     * Get trashed (deleted) incoming shipments
     */
    public function getTrashed(Request $request)
    {
        try {
            $companyId = $request->user()->company_id;
            $perPage = $request->get('per_page', 15);

            $query = Purchase::onlyTrashed()
                ->where('company_id', $companyId)
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->with([
                    'customer:id,first_name,second_name,email,mobile',
                    'currency:id,name,code,symbol',
                    'employee:id,first_name,last_name,employee_number',
                    'user:id,first_name,second_name,email'
                ])
                ->orderBy('deleted_at', 'desc');

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching deleted incoming shipments: ' . $e->getMessage());
        }
    }

    /**
     * Get sortable fields with their display names and current sort status
     */
    public function getSortableFields()
    {
        return [
            'id' => [
                'display_name' => 'ID',
                'field' => 'id',
                'sortable' => true,
                'type' => 'numeric'
            ],
            'invoice_number' => [
                'display_name' => 'Invoice Number',
                'field' => 'invoice_number',
                'sortable' => true,
                'type' => 'string'
            ],
            'date' => [
                'display_name' => 'Date',
                'field' => 'date',
                'sortable' => true,
                'type' => 'date'
            ],
            'time' => [
                'display_name' => 'Time',
                'field' => 'time',
                'sortable' => true,
                'type' => 'time'
            ],
            'due_date' => [
                'display_name' => 'Due Date',
                'field' => 'due_date',
                'sortable' => true,
                'type' => 'date'
            ],
            'customer_name' => [
                'display_name' => 'Customer Name',
                'field' => 'customer_name',
                'sortable' => true,
                'type' => 'string'
            ],
            'customer_email' => [
                'display_name' => 'Customer Email',
                'field' => 'customer_email',
                'sortable' => true,
                'type' => 'string'
            ],
            'customer_mobile' => [
                'display_name' => 'Customer Mobile',
                'field' => 'customer_mobile',
                'sortable' => true,
                'type' => 'string'
            ],
            'licensed_operator' => [
                'display_name' => 'Licensed Operator',
                'field' => 'licensed_operator',
                'sortable' => true,
                'type' => 'string'
            ],
            'total_amount' => [
                'display_name' => 'Total Amount',
                'field' => 'total_amount',
                'sortable' => true,
                'type' => 'numeric'
            ],
            'grand_total' => [
                'display_name' => 'Grand Total',
                'field' => 'grand_total',
                'sortable' => true,
                'type' => 'numeric'
            ],
            'status' => [
                'display_name' => 'Status',
                'field' => 'status',
                'sortable' => true,
                'type' => 'string'
            ],
            'ledger_code' => [
                'display_name' => 'Ledger Code',
                'field' => 'ledger_code',
                'sortable' => true,
                'type' => 'string'
            ],
            'ledger_number' => [
                'display_name' => 'Ledger Number',
                'field' => 'ledger_number',
                'sortable' => true,
                'type' => 'numeric'
            ],
            'exchange_rate' => [
                'display_name' => 'Exchange Rate',
                'field' => 'exchange_rate',
                'sortable' => true,
                'type' => 'numeric'
            ],
            'created_at' => [
                'display_name' => 'Created At',
                'field' => 'created_at',
                'sortable' => true,
                'type' => 'datetime'
            ],
            'updated_at' => [
                'display_name' => 'Updated At',
                'field' => 'updated_at',
                'sortable' => true,
                'type' => 'datetime'
            ]
        ];
    }

    /**
     * Get sorting options for frontend
     */
    public function getSortingOptions()
    {
        return [
            'default_sort' => 'created_at',
            'default_order' => 'desc',
            'available_orders' => [
                'asc' => 'Ascending',
                'desc' => 'Descending'
            ],
            'sortable_fields' => $this->getSortableFields()
        ];
    }
}
