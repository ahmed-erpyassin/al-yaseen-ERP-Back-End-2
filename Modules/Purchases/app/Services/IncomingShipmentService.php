<?php

namespace Modules\Purchases\app\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\app\Enums\SalesTypeEnum;
use Modules\Purchases\Http\Requests\IncomingShipmentRequest;
use Modules\Purchases\Http\Requests\OutgoingShipmentRequest;
use Modules\Purchases\Models\Purchase;

class IncomingShipmentService
{
    public function index(Request $request)
    {
        try {

            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Purchase::query()
                ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->when($customerSearch, function ($query, $customerSearch) {
                    $query->whereHas('customer', function ($q) use ($customerSearch) {
                        $q->where('name', 'like', '%' . $customerSearch . '%');
                    });
                })
                ->orderBy($sortBy, $sortOrder)
                ->get();
        } catch (\Exception $e) {
            throw new \Exception('Error fetching outgoing offers: ' . $e->getMessage());
        }
    }

    public function store(IncomingShipmentRequest $request)
    {

        try {
            return DB::transaction(function () use ($request) {
                $companyId = $request->company_id;
                $userId = Auth::id();

                if (!$userId) {
                    // Fallback to first user if no authenticated user (for testing/seeding)
                    $firstUser = \Modules\Users\Models\User::first();
                    if (!$firstUser) {
                        throw new \Exception('No users found in the system');
                    }
                    $userId = $firstUser->id;
                }

                $validatedData = $request->validated();
                $items = $validatedData['items'] ?? [];
                unset($validatedData['items']); // Remove items from main data

                $data = [
                    'type'       => PurchaseTypeEnum::INCOMING_SHIPMENT,
                    'company_id' => $companyId,
                    'user_id'    => $userId,
                    'status'     => 'draft',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $validatedData;

                $shipment = Purchase::create($data);

                // Create shipment items if provided
                if (!empty($items)) {
                    $this->createShipmentItems($shipment, $items);
                }

                return $shipment->load(['items', 'supplier', 'currency', 'creator']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified incoming shipment with all related data
     */
    public function show($id)
    {
        try {
            $shipment = Purchase::with([
                'items.item',
                'items.unit',
                'supplier',
                'customer',
                'currency',
                'branch',
                'creator',
                'updater'
            ])
            ->where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
            ->findOrFail($id);

            return $shipment;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified incoming shipment
     */
    public function update($request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $shipment = Purchase::where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                    ->findOrFail($id);

                $userId = Auth::id();

                if (!$userId) {
                    // Fallback to first user if no authenticated user (for testing/seeding)
                    $firstUser = \Modules\Users\Models\User::first();
                    if (!$firstUser) {
                        throw new \Exception('No users found in the system');
                    }
                    $userId = $firstUser->id;
                }

                $validatedData = $request->validated();
                $items = $validatedData['items'] ?? [];
                unset($validatedData['items']); // Remove items from main data

                // Add updated_by field
                $validatedData['updated_by'] = $userId;

                // Update the shipment
                $shipment->update($validatedData);

                // Update shipment items if provided
                if (!empty($items)) {
                    // Delete existing items
                    $shipment->items()->delete();

                    // Create new items
                    $this->createShipmentItems($shipment, $items);
                }

                return $shipment->load(['items', 'supplier', 'customer', 'currency', 'creator', 'updater']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified incoming shipment (soft delete)
     */
    public function destroy($id)
    {
        try {
            $shipment = Purchase::where('type', PurchaseTypeEnum::INCOMING_SHIPMENT)
                ->findOrFail($id);

            $userId = Auth::id();

            if (!$userId) {
                // Fallback to first user if no authenticated user (for testing/seeding)
                $firstUser = \Modules\Users\Models\User::first();
                if (!$firstUser) {
                    throw new \Exception('No users found in the system');
                }
                $userId = $firstUser->id;
            }

            // Update the deleted_by field before soft deleting
            $shipment->update(['updated_by' => $userId]);

            // Soft delete the shipment
            $shipment->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting incoming shipment: ' . $e->getMessage());
        }
    }

    /**
     * Create shipment items
     */
    private function createShipmentItems($shipment, $items)
    {
        foreach ($items as $index => $itemData) {
            $item = [
                'purchase_id' => $shipment->id,
                'serial_number' => $index + 1,
                'item_id' => $itemData['item_id'] ?? null,
                'account_id' => $itemData['account_id'] ?? null,
                'quantity' => $itemData['quantity'] ?? 1,
                'unit_price' => $itemData['unit_price'] ?? 0,
                'discount_rate' => $itemData['discount_rate'] ?? 0,
                'tax_rate' => $itemData['tax_rate'] ?? 0,
                'total_foreign' => $itemData['total_foreign'] ?? 0,
                'total_local' => $itemData['total_local'] ?? 0,
                'total' => $itemData['total'] ?? 0,
                'notes' => $itemData['notes'] ?? null,
                'description' => $itemData['description'] ?? null,
            ];

            \Modules\Purchases\Models\PurchaseItem::create($item);
        }
    }
}
