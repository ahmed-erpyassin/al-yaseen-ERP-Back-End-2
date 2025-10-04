<?php

namespace Modules\Sales\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Sales\app\Enums\SalesTypeEnum;
use Modules\Sales\Http\Requests\OutgoingShipmentRequest;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;
use Modules\Customers\Models\Customer;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\StockMovement;
use Carbon\Carbon;

class OutgoingShipmentService
{
    /**
     * Get paginated list of outgoing shipments with advanced search and filters
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            // Advanced search parameters
            $shipmentNumberFrom = $request->get('shipment_number_from');
            $shipmentNumberTo = $request->get('shipment_number_to');
            $invoiceNumber = $request->get('invoice_number');
            $customerName = $request->get('customer_name');
            $licensedOperator = $request->get('licensed_operator');
            $exactDate = $request->get('exact_date');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            // Legacy search parameters (keep existing functionality)
            $customerSearch = $request->get('customer_search');
            $warehouseId = $request->get('warehouse_id');

            // Sorting parameters
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            $query = Sale::with([
                'customer',
                'items.item',
                'items.unit',
                'items.warehouse',
                'user',
                'employee',
                'currency',
                'branch'
            ])->where('type', SalesTypeEnum::OUTGOING_SHIPMENT);

            // General search (existing functionality)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('book_code', 'like', "%{$search}%")
                      ->orWhereHas('customer', function ($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // Advanced search filters

            // Shipment Number range search (from/to)
            if ($shipmentNumberFrom) {
                $query->where('invoice_number', '>=', $shipmentNumberFrom);
            }
            if ($shipmentNumberTo) {
                $query->where('invoice_number', '<=', $shipmentNumberTo);
            }

            // Invoice Number search
            if ($invoiceNumber) {
                $query->where('invoice_number', 'like', "%{$invoiceNumber}%");
            }

            // Customer Name search
            if ($customerName) {
                $query->whereHas('customer', function ($q) use ($customerName) {
                    $q->where('name', 'like', "%{$customerName}%");
                });
            }

            // Licensed Operator search
            if ($licensedOperator) {
                $query->where('licensed_operator', 'like', "%{$licensedOperator}%");
            }

            // Date search - exact date
            if ($exactDate) {
                $query->whereDate('date', $exactDate);
            }

            // Date range search (from/to)
            if ($dateFrom && !$exactDate) {
                $query->whereDate('date', '>=', $dateFrom);
            }
            if ($dateTo && !$exactDate) {
                $query->whereDate('date', '<=', $dateTo);
            }

            // Legacy filters (keep existing functionality)
            if ($customerSearch) {
                $query->whereHas('customer', function ($q) use ($customerSearch) {
                    $q->where('name', 'like', '%' . $customerSearch . '%');
                });
            }

            if ($warehouseId) {
                $query->whereHas('items', function ($q) use ($warehouseId) {
                    $q->where('warehouse_id', $warehouseId);
                });
            }

            // Validate and apply sorting
            $allowedSortFields = [
                'id', 'book_code', 'invoice_number', 'date', 'time', 'due_date',
                'customer_id', 'customer_email', 'licensed_operator', 'status',
                'notes', 'created_at', 'updated_at'
            ];

            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $result = $query->paginate($perPage);

            // Add search parameters to the result for reference
            $result->appends($request->all());

            return $result;

        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing shipments: ' . $e->getMessage());
        }
    }

    /**
     * Create a new outgoing shipment
     */
    public function store(OutgoingShipmentRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $companyId = Auth::user()->company_id ?? 1;
                $userId = Auth::id();
                $validatedData = $request->validated();

                // Generate book code and invoice number for outgoing shipments
                $numberingData = $this->generateBookAndInvoiceNumber($companyId);

                // Get customer data for auto-population
                $customer = Customer::find($validatedData['customer_id']);

                // Prepare shipment data
                $shipmentData = [
                    'type' => SalesTypeEnum::OUTGOING_SHIPMENT,
                    'company_id' => $companyId,
                    'user_id' => $userId,
                    'created_by' => $userId,
                    'status' => $validatedData['status'] ?? 'draft',

                    // Auto-generated fields
                    'book_code' => $numberingData['book_code'],
                    'invoice_number' => $numberingData['invoice_number'],
                    'date' => Carbon::now()->toDateString(),
                    'time' => Carbon::now()->toTimeString(),

                    // Customer data
                    'customer_id' => $validatedData['customer_id'],
                    'customer_email' => $customer ? $customer->email : $validatedData['customer_email'] ?? null,

                    // Required fields with defaults
                    'currency_id' => $validatedData['currency_id'],
                    'branch_id' => $validatedData['branch_id'] ?? 1,
                    'employee_id' => $validatedData['employee_id'] ?? 1,
                    'journal_number' => $validatedData['journal_number'] ?? 1,
                    'exchange_rate' => 1.0000,
                    'total_foreign' => floatval($validatedData['total_local'] ?? 0),
                    'total_local' => floatval($validatedData['total_local'] ?? 0),
                    'total_amount' => floatval($validatedData['total_local'] ?? 0),

                    // Other fields from request
                    'due_date' => $validatedData['due_date'] ?? Carbon::now()->addDays(30)->format('Y-m-d'),
                    'notes' => $validatedData['notes'] ?? null,
                    'licensed_operator' => $validatedData['licensed_operator'] ?? null,
                ];

                // Create the shipment
                $shipment = Sale::create($shipmentData);

                // Create shipment items and handle inventory deduction
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    $this->createShipmentItems($shipment, $validatedData['items']);
                }

                return $shipment->load(['customer', 'items.item', 'items.unit', 'user']);
            });

        } catch (Exception $e) {
            Log::error('Error creating outgoing shipment: ' . $e->getMessage());
            throw new \Exception('Error creating outgoing shipment: ' . $e->getMessage());
        }
    }

    /**
     * Show a specific outgoing shipment
     */
    public function show($id)
    {
        try {
            return Sale::with([
                'customer',
                'items.item',
                'items.unit',
                'user',
                'employee',
                'currency'
            ])
            ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
            ->findOrFail($id);

        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing shipment: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing outgoing shipment - Complete update functionality
     */
    public function update(OutgoingShipmentRequest $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $shipment = Sale::with(['items', 'customer'])
                    ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
                    ->findOrFail($id);

                // Check if shipment can be updated
                if ($shipment->status === 'shipped') {
                    throw new \Exception('Cannot update shipped outgoing shipment');
                }

                $validatedData = $request->validated();
                $userId = Auth::id();
                $companyId = Auth::user()->company_id ?? $shipment->company_id;

                // Get customer data for auto-population if customer changed
                $customer = null;
                if (isset($validatedData['customer_id']) && $validatedData['customer_id'] != $shipment->customer_id) {
                    $customer = Customer::find($validatedData['customer_id']);
                }

                // Prepare complete update data
                $updateData = [
                    'updated_by' => $userId,
                    'company_id' => $companyId,

                    // Customer information
                    'customer_id' => $validatedData['customer_id'] ?? $shipment->customer_id,
                    'customer_email' => $validatedData['customer_email'] ??
                                      ($customer ? $customer->email : $shipment->customer_email),

                    // Employee and branch information
                    'employee_id' => $validatedData['employee_id'] ?? $shipment->employee_id,
                    'branch_id' => $validatedData['branch_id'] ?? $shipment->branch_id,

                    // Dates and notes
                    'due_date' => $validatedData['due_date'] ?? $shipment->due_date,
                    'notes' => $validatedData['notes'] ?? $shipment->notes,

                    // Licensed operator (if provided)
                    'licensed_operator' => $validatedData['licensed_operator'] ?? $shipment->licensed_operator,

                    // Status can be updated if provided
                    'status' => $validatedData['status'] ?? $shipment->status,
                ];

                // Update the shipment
                $shipment->update($updateData);

                // Update shipment items if provided
                if (isset($validatedData['items']) && is_array($validatedData['items'])) {
                    // First, restore inventory for existing items
                    $this->restoreInventoryForShipment($shipment);

                    // Delete existing items (soft delete)
                    $shipment->items()->delete();

                    // Create new items and deduct inventory
                    $this->createShipmentItems($shipment, $validatedData['items']);
                }

                // Reload with all relationships for complete response
                return $shipment->load([
                    'customer',
                    'items.item',
                    'items.unit',
                    'items.warehouse',
                    'user',
                    'employee',
                    'currency',
                    'branch'
                ]);
            });

        } catch (Exception $e) {
            Log::error('Error updating outgoing shipment: ' . $e->getMessage());
            throw new \Exception('Error updating outgoing shipment: ' . $e->getMessage());
        }
    }

    /**
     * Delete an outgoing shipment (soft delete with proper inventory restoration)
     */
    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $shipment = Sale::with(['items'])
                    ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
                    ->findOrFail($id);

                // Check if shipment can be deleted
                if ($shipment->status === 'shipped') {
                    throw new \Exception('Cannot delete shipped outgoing shipment');
                }

                // Set deleted_by before soft delete
                $shipment->update([
                    'deleted_by' => Auth::id(),
                    'status' => 'cancelled'
                ]);

                // Restore inventory before deletion
                $this->restoreInventoryForShipment($shipment);

                // Soft delete the shipment items first
                foreach ($shipment->items as $item) {
                    $item->update(['deleted_by' => Auth::id()]);
                    $item->delete();
                }

                // Soft delete the shipment
                $shipment->delete();

                return [
                    'success' => true,
                    'message' => 'Outgoing shipment deleted successfully',
                    'shipment_id' => $id,
                    'invoice_number' => $shipment->invoice_number
                ];
            });

        } catch (Exception $e) {
            Log::error('Error deleting outgoing shipment: ' . $e->getMessage());
            throw new \Exception('Error deleting outgoing shipment: ' . $e->getMessage());
        }
    }

    /**
     * Restore a soft-deleted outgoing shipment
     */
    public function restore($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $shipment = Sale::withTrashed()
                    ->with(['items' => function($query) {
                        $query->withTrashed();
                    }])
                    ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
                    ->findOrFail($id);

                if (!$shipment->trashed()) {
                    throw new \Exception('Outgoing shipment is not deleted');
                }

                // Check inventory availability before restoring
                foreach ($shipment->items as $item) {
                    $this->validateInventoryForRestore($item);
                }

                // Restore the shipment
                $shipment->restore();
                $shipment->update([
                    'status' => 'draft',
                    'deleted_by' => null
                ]);

                // Restore items and deduct inventory again
                foreach ($shipment->items as $item) {
                    $item->restore();
                    $item->update(['deleted_by' => null]);

                    // Deduct inventory again
                    $this->deductFromWarehouse(
                        $item->item_id,
                        $item->warehouse_id,
                        $item->quantity,
                        $item->unit_id,
                        $shipment
                    );
                }

                return $shipment->load([
                    'customer',
                    'items.item',
                    'items.unit',
                    'items.warehouse',
                    'user',
                    'employee'
                ]);
            });

        } catch (Exception $e) {
            Log::error('Error restoring outgoing shipment: ' . $e->getMessage());
            throw new \Exception('Error restoring outgoing shipment: ' . $e->getMessage());
        }
    }

    /**
     * Validate inventory availability for restoring a shipment item
     */
    private function validateInventoryForRestore($saleItem)
    {
        // Find the item
        $item = Item::find($saleItem->item_id);

        if (!$item) {
            throw new \Exception("Item {$saleItem->item_id} not found");
        }

        // If stock tracking is disabled, no validation needed
        if (!$item->stock_tracking) {
            return;
        }

        // Check if sufficient quantity is available
        if ($item->balance < $saleItem->quantity) {
            throw new \Exception("Insufficient inventory to restore shipment. Available: {$item->balance}, Required: {$saleItem->quantity}");
        }
    }

    /**
     * Generate book code and invoice number for outgoing shipments
     */
    private function generateBookAndInvoiceNumber($companyId): array
    {
        // Get the last shipment for this company
        $lastShipment = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
            ->orderBy('id', 'desc')
            ->first();

        // Generate book code
        $bookCode = $this->generateBookCode($companyId);

        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber($companyId);

        return [
            'book_code' => $bookCode,
            'invoice_number' => $invoiceNumber
        ];
    }

    /**
     * Generate book code for outgoing shipments (50 shipments per book)
     */
    private function generateBookCode($companyId): string
    {
        // Get the last book code for this company and type
        $lastShipment = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
            ->whereNotNull('book_code')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastShipment || !$lastShipment->book_code) {
            return 'SHIP-BOOK-001';
        }

        // Extract the number from the book code (e.g., SHIP-BOOK-001 -> 001)
        $lastNumber = (int) substr($lastShipment->book_code, -3);

        // Check if current book has reached 50 shipments
        $currentBookShipmentsCount = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
            ->where('book_code', $lastShipment->book_code)
            ->count();

        if ($currentBookShipmentsCount >= 50) {
            // Start new book
            $newNumber = $lastNumber + 1;
            return 'SHIP-BOOK-' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }

        // Continue with current book
        return $lastShipment->book_code;
    }

    /**
     * Generate sequential invoice number for outgoing shipments
     */
    private function generateInvoiceNumber($companyId): string
    {
        // Get the last invoice number for this company and type
        $lastShipment = Sale::where('company_id', $companyId)
            ->where('type', SalesTypeEnum::OUTGOING_SHIPMENT)
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastShipment) {
            return 'SHIP-000001';
        }

        // Extract the number from the invoice number (e.g., SHIP-000001 -> 1)
        $lastNumber = (int) substr($lastShipment->invoice_number, -6);
        $newNumber = $lastNumber + 1;

        return 'SHIP-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create shipment items and handle inventory deduction
     */
    private function createShipmentItems($shipment, $items)
    {
        $serialNumber = 1;

        foreach ($items as $itemData) {
            // Get item details
            $item = Item::find($itemData['item_id']);
            $unit = Unit::find($itemData['unit_id']);

            // Create sale item
            $saleItemData = [
                'sale_id' => $shipment->id,
                'serial_number' => $serialNumber++,
                'item_id' => $itemData['item_id'],
                'item_number' => $item ? $item->item_number : $itemData['item_number'] ?? null,
                'item_name' => $item ? $item->name : $itemData['item_name'] ?? null,
                'unit_id' => $itemData['unit_id'],
                'unit_name' => $unit ? $unit->name : $itemData['unit_name'] ?? null,
                'quantity' => $itemData['quantity'],
                'warehouse_id' => $itemData['warehouse_id'],
                'notes' => $itemData['notes'] ?? null,

                // Financial fields
                'description' => $itemData['description'] ?? null,
                'unit_price' => $itemData['unit_price'] ?? 0,
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'total_foreign' => $itemData['total_foreign'] ?? 0,
                'total_local' => $itemData['total_local'] ?? 0,
                'total' => $itemData['total'] ?? 0,
            ];

            $saleItem = SaleItem::create($saleItemData);

            // Deduct from warehouse inventory
            $this->deductFromWarehouse(
                $itemData['item_id'],
                $itemData['warehouse_id'],
                $itemData['quantity'],
                $itemData['unit_id'],
                $shipment
            );
        }
    }

    /**
     * Deduct quantity from warehouse inventory
     */
    private function deductFromWarehouse($itemId, $warehouseId, $quantity, $unitId, $shipment)
    {
        // Find the item
        $item = Item::find($itemId);
        if (!$item) {
            throw new \Exception("Item {$itemId} not found");
        }

        // Handle null warehouse_id - use default warehouse or skip stock movement
        if (!$warehouseId) {
            // Try to get the first available warehouse for the company
            $defaultWarehouse = Warehouse::where('company_id', $shipment->company_id)->first();
            if ($defaultWarehouse) {
                $warehouseId = $defaultWarehouse->id;
            } else {
                // If no warehouse found and stock tracking is enabled, throw error
                if ($item->stock_tracking) {
                    throw new \Exception("No warehouse specified and no default warehouse found for company");
                }
                // If stock tracking is disabled, skip inventory management
                return;
            }
        }

        // Check if item has stock tracking enabled
        if (!$item->stock_tracking) {
            // If stock tracking is disabled, just create the movement record
            StockMovement::create([
                'company_id' => $shipment->company_id,
                'user_id' => $shipment->user_id,
                'warehouse_id' => $warehouseId,
                'document_id' => $shipment->id,
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'type' => 'sales',
                'movement_type' => 'out',
                'quantity' => $quantity,
                'transaction_date' => now(),
                'notes' => "Outgoing shipment: {$shipment->invoice_number}",
                'created_by' => $shipment->user_id,
            ]);
            return;
        }

        // Check if sufficient quantity is available
        if ($item->balance < $quantity) {
            throw new \Exception("Insufficient stock. Available: {$item->balance}, Required: {$quantity}");
        }

        // Deduct quantity from item balance
        $item->balance -= $quantity;
        $item->quantity -= $quantity;
        $item->updated_by = $shipment->user_id;
        $item->save();

        // Create stock movement record
        StockMovement::create([
            'company_id' => $shipment->company_id,
            'user_id' => $shipment->user_id,
            'warehouse_id' => $warehouseId,
            'document_id' => $shipment->id,
            'item_id' => $itemId,
            'unit_id' => $unitId,
            'type' => 'sales',
            'movement_type' => 'out',
            'quantity' => $quantity,
            'transaction_date' => now(),
            'notes' => "Outgoing shipment: {$shipment->invoice_number}",
            'created_by' => $shipment->user_id,
        ]);
    }

    /**
     * Restore inventory for a shipment (used when updating or deleting)
     */
    private function restoreInventoryForShipment($shipment)
    {
        foreach ($shipment->items as $saleItem) {
            if ($saleItem->item_id) {
                // Find the item
                $item = Item::find($saleItem->item_id);
                if ($item && $item->stock_tracking) {
                    // Handle null warehouse_id
                    $warehouseId = $saleItem->warehouse_id;
                    if (!$warehouseId) {
                        // Try to get the first available warehouse for the company
                        $defaultWarehouse = Warehouse::where('company_id', $shipment->company_id)->first();
                        if ($defaultWarehouse) {
                            $warehouseId = $defaultWarehouse->id;
                        } else {
                            // Skip if no warehouse found
                            continue;
                        }
                    }

                    // Restore quantity to item balance
                    $item->balance += $saleItem->quantity;
                    $item->quantity += $saleItem->quantity;
                    $item->updated_by = $shipment->user_id;
                    $item->save();

                    // Create reverse stock movement record
                    StockMovement::create([
                        'company_id' => $shipment->company_id,
                        'user_id' => $shipment->user_id,
                        'warehouse_id' => $warehouseId,
                        'document_id' => $shipment->id,
                        'item_id' => $saleItem->item_id,
                        'unit_id' => $saleItem->unit_id,
                        'type' => 'sales',
                        'movement_type' => 'in',
                        'quantity' => $saleItem->quantity,
                        'transaction_date' => now(),
                        'notes' => "Restored from shipment: {$shipment->invoice_number}",
                        'created_by' => $shipment->user_id,
                    ]);
                }
            }
        }
    }

    /**
     * Search for customers
     */
    public function searchCustomers(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        return Customer::where('first_name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->limit($limit)
            ->get(['id', 'first_name', 'email', 'phone']);
    }

    /**
     * Search for items
     */
    public function searchItems(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 10);

        return Item::with('unit')
            ->where('name', 'like', "%{$search}%")
            ->orWhere('item_number', 'like', "%{$search}%")
            ->limit($limit)
            ->get(['id', 'item_number', 'name', 'unit_id']);
    }

    /**
     * Get form data for creating/editing outgoing shipments
     */
    public function getFormData()
    {
        try {
            return [
                'customers' => Customer::select('id', 'email', 'phone')->get(),
                'items' => Item::with('unit')->select('id', 'item_number', 'name', 'unit_id')->get(),
                'warehouses' => Warehouse::select('id', 'name')->get(),
                'employees' => \Modules\HumanResources\Models\Employee::select('id',  'employee_number')->get(),
                'currencies' => \Modules\FinancialAccounts\Models\Currency::select('id', 'name', 'code', 'symbol')->get(),
                'units' => Unit::select('id', 'name', 'symbol')->get(),
                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'pending', 'label' => 'Pending'],
                    ['value' => 'shipped', 'label' => 'Shipped'],
                    ['value' => 'delivered', 'label' => 'Delivered'],
                    ['value' => 'cancelled', 'label' => 'Cancelled'],
                ],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching form data: ' . $e->getMessage());
        }
    }


}
