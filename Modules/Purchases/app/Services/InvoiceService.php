<?php

namespace Modules\Purchases\app\Services;

use App\Models\SalesInvoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Purchases\app\Enums\PurchaseTypeEnum;
use Modules\Purchases\Http\Requests\InvoiceRequest;
use Modules\Purchases\Models\Purchase;

class InvoiceService
{
    public function index(Request $request)
    {
        try {

            $customerSearch = $request->get('customer_search', null);
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            return Purchase::query()
                ->where('type', PurchaseTypeEnum::INVOICE)
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

    public function store(InvoiceRequest $request)
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
                    'type'       => PurchaseTypeEnum::INVOICE,
                    'company_id' => $companyId,
                    'user_id'    => $userId,
                    'status'     => 'draft',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ] + $validatedData;

                $invoice = Purchase::create($data);

                // Create invoice items if provided
                if (!empty($items)) {
                    $this->createInvoiceItems($invoice, $items);
                }

                return $invoice->load(['items', 'supplier', 'customer', 'currency', 'creator']);
            });
        } catch (Exception $e) {
            throw new \Exception('Error creating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice with all related data
     */
    public function show($id)
    {
        try {
            $invoice = Purchase::with([
                'items.item',
                'items.unit',
                'supplier',
                'customer',
                'currency',
                'branch',
                'creator',
                'updater'
            ])
            ->where('type', PurchaseTypeEnum::INVOICE)
            ->findOrFail($id);

            return $invoice;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching invoice: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified invoice
     */
    public function update($request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $invoice = Purchase::where('type', PurchaseTypeEnum::INVOICE)
                    ->findOrFail($id);

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

                // Add updated_by field and company_id
                $validatedData['updated_by'] = $userId;
                $validatedData['company_id'] = $companyId;

                // Update the invoice
                $invoice->update($validatedData);

                // Update invoice items if provided
                if (!empty($items)) {
                    // Delete existing items
                    $invoice->items()->delete();

                    // Create new items
                    $this->createInvoiceItems($invoice, $items);
                }

                return $invoice->load(['items', 'supplier', 'customer', 'currency', 'creator', 'updater']);
            });
        } catch (\Exception $e) {
            throw new \Exception('Error updating invoice: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified invoice (soft delete)
     */
    public function destroy($id)
    {
        try {
            $invoice = Purchase::where('type', PurchaseTypeEnum::INVOICE)
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

            // Update the updated_by field before soft deleting
            $invoice->update(['updated_by' => $userId]);

            // Soft delete the invoice
            $invoice->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting invoice: ' . $e->getMessage());
        }
    }

    /**
     * Create invoice items
     */
    private function createInvoiceItems($invoice, $items)
    {
        foreach ($items as $index => $itemData) {
            $item = [
                'purchase_id' => $invoice->id,
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
