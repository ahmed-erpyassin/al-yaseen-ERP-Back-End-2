<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Http\Requests\StoreManufacturingFormulaRequest;
use Modules\Inventory\Http\Requests\UpdateManufacturingFormulaRequest;
use Modules\Inventory\Http\Resources\ManufacturingFormulaResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManufacturingFormulaController extends Controller
{
    /**
     * Display a listing of manufacturing formulas with advanced search and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $query = BomItem::with(['item', 'creator', 'updater'])
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number'); // Only manufacturing formulas

            // ✅ Advanced Search Functionality
            $this->applySearchFilters($query, $request);

            // ✅ Universal Sorting System
            $this->applySorting($query, $request);

            // ✅ Pagination
            $perPage = $request->get('per_page', 15);
            $formulas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ManufacturingFormulaResource::collection($formulas->items()),
                'pagination' => [
                    'current_page' => $formulas->currentPage(),
                    'last_page' => $formulas->lastPage(),
                    'per_page' => $formulas->perPage(),
                    'total' => $formulas->total(),
                    'from' => $formulas->firstItem(),
                    'to' => $formulas->lastItem(),
                ],
                'message' => 'Manufacturing formulas retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving manufacturing formulas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving manufacturing formulas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified manufacturing formula.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::with(['item', 'creator', 'updater'])
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ManufacturingFormulaResource($formula),
                'message' => 'Manufacturing formula retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Manufacturing formula not found'
            ], 404);
        }
    }

    /**
     * Update the specified manufacturing formula.
     */
    public function update(UpdateManufacturingFormulaRequest $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            $updateData = $request->validated();
            $updateData['updated_by'] = $user->id;

            // Update item information if item_id changed
            if (isset($updateData['item_id']) && $updateData['item_id'] !== $formula->item_id) {
                $item = Item::find($updateData['item_id']);
                if ($item) {
                    $updateData['item_number'] = $item->item_number;
                    $updateData['item_name'] = $item->name;
                }
            }

            // Update unit information if unit_id changed
            if (isset($updateData['unit_id']) && $updateData['unit_id'] !== $formula->unit_id) {
                $unit = Unit::find($updateData['unit_id']);
                if ($unit) {
                    $updateData['unit_name'] = $unit->name;
                    $updateData['unit_code'] = $unit->code;
                }
            }

            // Recalculate costs if relevant fields changed
            if (isset($updateData['labor_cost']) || isset($updateData['operating_cost']) ||
                isset($updateData['waste_cost']) || isset($updateData['selected_purchase_price_type'])) {

                $selectedPurchasePrice = $this->getSelectedPurchasePrice(array_merge($formula->toArray(), $updateData));
                $updateData['final_cost'] = $this->calculateFinalCost(array_merge($formula->toArray(), $updateData), $selectedPurchasePrice);
                $updateData['material_cost'] = $selectedPurchasePrice;
                $updateData['total_production_cost'] = $updateData['final_cost'];

                if (isset($updateData['produced_quantity']) && $updateData['produced_quantity'] > 0) {
                    $updateData['cost_per_unit'] = $updateData['final_cost'] / $updateData['produced_quantity'];
                }
            }

            $formula->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ManufacturingFormulaResource($formula->load(['item', 'creator', 'updater'])),
                'message' => 'Manufacturing formula updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified manufacturing formula (soft delete).
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            // Soft delete with audit trail
            $formula->update([
                'deleted_by' => $user->id,
                'deleted_at' => now(),
            ]);

            $formula->delete(); // This will set deleted_at if using SoftDeletes trait

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing formula deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get all Item Numbers for dropdown simulation.
     */
    public function getItemNumbers(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;

        $items = Item::where('company_id', $companyId)
            ->select('id', 'item_number', 'name', 'description')
            ->orderBy('item_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'description' => $item->description
                ];
            }),
            'message' => 'Item numbers retrieved successfully',
            'message_ar' => 'تم استرداد أرقام الأصناف بنجاح'
        ]);
    }

    /**
     * ✅ Get Item details by Item Number or Name.
     */
    public function getItemDetails(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $itemNumber = $request->get('item_number');
        $itemName = $request->get('item_name');

        if (!$itemNumber && !$itemName) {
            return response()->json([
                'success' => false,
                'message' => 'Either item_number or item_name is required',
                'message_ar' => 'رقم الصنف أو اسم الصنف مطلوب'
            ], 400);
        }

        $query = Item::where('company_id', $companyId);

        if ($itemNumber) {
            $query->where('item_number', $itemNumber);
        } elseif ($itemName) {
            $query->where('name', 'like', '%' . $itemName . '%');
        }

        $item = $query->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
                'message_ar' => 'الصنف غير موجود'
            ], 404);
        }

        // ✅ Get purchase prices from invoices (placeholder - implement when invoice tables available)
        $purchasePrices = $this->getPurchasePricesFromInvoices($item->id);

        // ✅ Get selling prices from invoices (placeholder - implement when invoice tables available)
        $sellingPrices = $this->getSellingPricesFromInvoices($item->id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'item_number' => $item->item_number,
                'item_name' => $item->name,
                'description' => $item->description,
                'balance' => $item->balance ?? 0,
                'minimum_limit' => $item->minimum_limit ?? 0,
                'maximum_limit' => $item->maximum_limit ?? 0,
                'minimum_reorder_level' => $item->minimum_reorder_level ?? 0,
                'current_selling_price' => $item->selling_price ?? 0,
                'current_purchase_price' => $item->purchase_price ?? 0,

                // ✅ Purchase prices from invoices
                'first_purchase_price' => $purchasePrices['latest'] ?? 0,
                'second_purchase_price' => $purchasePrices['median'] ?? 0,
                'third_purchase_price' => $purchasePrices['earliest'] ?? 0,

                // ✅ Selling prices from invoices
                'first_selling_price' => $sellingPrices['latest'] ?? 0,
                'second_selling_price' => $sellingPrices['median'] ?? 0,
                'third_selling_price' => $sellingPrices['earliest'] ?? 0,
            ],
            'message' => 'Item details retrieved successfully',
            'message_ar' => 'تم استرداد تفاصيل الصنف بنجاح'
        ]);
    }

    /**
     * ✅ Store Manufacturing Formula via API.
     */
    public function store(StoreManufacturingFormulaRequest $request): JsonResponse
    {
        $companyId = Auth::user()->company_id ?? $request->company_id;
        $userId = Auth::id() ?? $request->user_id;

        try {
            DB::beginTransaction();

            // ✅ Get validated data
            $data = $request->validated();

            // ✅ Set system fields
            $data['company_id'] = $companyId;
            $data['user_id'] = $userId;
            $data['created_by'] = $userId;

            // ✅ Auto-generate formula number if not provided
            if (empty($data['formula_number'])) {
                $data['formula_number'] = $this->generateFormulaNumber($companyId);
            }

            // ✅ Set automatic date and time on insert
            $data['formula_date'] = now()->toDateString();
            $data['formula_time'] = now()->toTimeString();
            $data['formula_datetime'] = now();

            // ✅ Get item information from Items table
            $item = Item::find($data['item_id']);
            if ($item) {
                $data['item_number'] = $item->item_number;
                $data['item_name'] = $item->name;
                $data['balance'] = $item->balance ?? 0;
                $data['minimum_limit'] = $item->minimum_limit ?? 0;
                $data['maximum_limit'] = $item->maximum_limit ?? 0;
                $data['minimum_reorder_level'] = $item->minimum_reorder_level ?? 0;
                $data['selling_price'] = $item->selling_price ?? 0;
                $data['purchase_price'] = $item->purchase_price ?? 0;

                // ✅ Get historical prices from invoices
                $purchasePrices = $this->getPurchasePricesFromInvoices($item->id);
                $sellingPrices = $this->getSellingPricesFromInvoices($item->id);

                $data['first_purchase_price'] = $purchasePrices['latest'] ?? 0;
                $data['second_purchase_price'] = $purchasePrices['median'] ?? 0;
                $data['third_purchase_price'] = $purchasePrices['earliest'] ?? 0;

                $data['first_selling_price'] = $sellingPrices['latest'] ?? 0;
                $data['second_selling_price'] = $sellingPrices['median'] ?? 0;
                $data['third_selling_price'] = $sellingPrices['earliest'] ?? 0;
            }

            // ✅ Get unit information from Units table
            if (!empty($data['unit_id'])) {
                $unit = Unit::find($data['unit_id']);
                if ($unit) {
                    $data['unit_name'] = $unit->name;
                    $data['unit_code'] = $unit->code;
                }
            }

            // ✅ Calculate Final Cost based on selected purchase price
            $selectedPurchasePrice = $this->getSelectedPurchasePrice($data);
            $data['final_cost'] = $this->calculateFinalCost($data, $selectedPurchasePrice);

            // ✅ Set additional cost fields
            $data['material_cost'] = $selectedPurchasePrice;
            $data['total_production_cost'] = $data['final_cost'];
            $data['cost_per_unit'] = $data['produced_quantity'] > 0 ?
                $data['final_cost'] / $data['produced_quantity'] : 0;

            // ✅ Set default values
            $data['status'] = $data['status'] ?? 'active';
            $data['is_active'] = $data['is_active'] ?? true;
            $data['component_type'] = 'raw_material'; // Default for manufacturing formula
            $data['sequence_order'] = 1;

            // ✅ Create manufacturing formula (stored in bom_items table)
            $formula = BomItem::create($data);

            // ✅ Load relationships for response
            $formula->load(['item', 'unit', 'creator']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'formula_name' => $formula->formula_name,
                    'formula_description' => $formula->formula_description,

                    // ✅ Item information
                    'item_id' => $formula->item_id,
                    'item_number' => $formula->item_number,
                    'item_name' => $formula->item_name,
                    'balance' => $formula->balance,
                    'minimum_limit' => $formula->minimum_limit,
                    'maximum_limit' => $formula->maximum_limit,
                    'minimum_reorder_level' => $formula->minimum_reorder_level,

                    // ✅ Unit information
                    'unit_id' => $formula->unit_id,
                    'unit_name' => $formula->unit_name,
                    'unit_code' => $formula->unit_code,

                    // ✅ Date and Time (automatic)
                    'formula_date' => $formula->formula_date,
                    'formula_time' => $formula->formula_time,
                    'formula_datetime' => $formula->formula_datetime,

                    // ✅ Quantities (manual input)
                    'consumed_quantity' => $formula->consumed_quantity,
                    'produced_quantity' => $formula->produced_quantity,

                    // ✅ Purchase prices from invoices
                    'first_purchase_price' => $formula->first_purchase_price,
                    'second_purchase_price' => $formula->second_purchase_price,
                    'third_purchase_price' => $formula->third_purchase_price,
                    'selected_purchase_price' => $selectedPurchasePrice,

                    // ✅ Selling prices from invoices
                    'first_selling_price' => $formula->first_selling_price,
                    'second_selling_price' => $formula->second_selling_price,
                    'third_selling_price' => $formula->third_selling_price,

                    // ✅ Costs (manual input)
                    'labor_cost' => $formula->labor_cost,
                    'operating_cost' => $formula->operating_cost,
                    'waste_cost' => $formula->waste_cost,

                    // ✅ Final Cost (calculated)
                    'final_cost' => $formula->final_cost,
                    'material_cost' => $formula->material_cost,
                    'total_production_cost' => $formula->total_production_cost,
                    'cost_per_unit' => $formula->cost_per_unit,

                    // ✅ Status
                    'status' => $formula->status,
                    'is_active' => $formula->is_active,

                    // ✅ Timestamps
                    'created_at' => $formula->created_at,
                    'updated_at' => $formula->updated_at,
                ],
                'message' => 'Manufacturing formula created successfully',
                'message_ar' => 'تم إنشاء معادلة التصنيع بنجاح'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create manufacturing formula: ' . $e->getMessage(),
                'message_ar' => 'فشل في إنشاء معادلة التصنيع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Generate unique formula number.
     */
    private function generateFormulaNumber($companyId): string
    {
        $prefix = 'MF-';
        $year = date('Y');
        $month = date('m');

        // Get the last formula number for this company
        $lastFormula = BomItem::where('company_id', $companyId)
            ->where('formula_number', 'like', "{$prefix}{$year}{$month}%")
            ->orderBy('formula_number', 'desc')
            ->first();

        if ($lastFormula) {
            // Extract the sequence number and increment
            $lastNumber = substr($lastFormula->formula_number, -4);
            $nextNumber = str_pad((int)$lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return "{$prefix}{$year}{$month}-{$nextNumber}";
    }

    /**
     * ✅ Get purchase prices from invoices (latest, median, earliest).
     */
    private function getPurchasePricesFromInvoices($itemId): array
    {
        // ✅ TODO: Implement actual queries when purchase invoice tables are available
        // This is a placeholder implementation

        // Example implementation when purchase_invoice_items table exists:
        /*
        $prices = DB::table('purchase_invoice_items')
            ->join('purchase_invoices', 'purchase_invoice_items.invoice_id', '=', 'purchase_invoices.id')
            ->where('purchase_invoice_items.item_id', $itemId)
            ->orderBy('purchase_invoices.invoice_date', 'desc')
            ->pluck('purchase_invoice_items.unit_price')
            ->toArray();

        if (empty($prices)) {
            return ['latest' => 0, 'median' => 0, 'earliest' => 0];
        }

        return [
            'latest' => $prices[0] ?? 0,
            'median' => $prices[floor(count($prices) / 2)] ?? 0,
            'earliest' => end($prices) ?? 0
        ];
        */

        // ✅ Placeholder values - replace with actual implementation
        return [
            'latest' => 25.50,   // Latest purchase price
            'median' => 24.75,   // Median purchase price
            'earliest' => 23.00  // Earliest purchase price
        ];
    }

    /**
     * ✅ Get selling prices from invoices (latest, median, earliest).
     */
    private function getSellingPricesFromInvoices($itemId): array
    {
        // ✅ TODO: Implement actual queries when sales invoice tables are available
        // This is a placeholder implementation

        // Example implementation when sales_invoice_items table exists:
        /*
        $prices = DB::table('sales_invoice_items')
            ->join('sales_invoices', 'sales_invoice_items.invoice_id', '=', 'sales_invoices.id')
            ->where('sales_invoice_items.item_id', $itemId)
            ->orderBy('sales_invoices.invoice_date', 'desc')
            ->pluck('sales_invoice_items.unit_price')
            ->toArray();

        if (empty($prices)) {
            return ['latest' => 0, 'median' => 0, 'earliest' => 0];
        }

        return [
            'latest' => $prices[0] ?? 0,
            'median' => $prices[floor(count($prices) / 2)] ?? 0,
            'earliest' => end($prices) ?? 0
        ];
        */

        // ✅ Placeholder values - replace with actual implementation
        return [
            'latest' => 35.00,   // Latest selling price
            'median' => 34.25,   // Median selling price
            'earliest' => 33.50  // Earliest selling price
        ];
    }

    /**
     * ✅ Get selected purchase price based on user choice.
     */
    private function getSelectedPurchasePrice($data): float
    {
        $priceSelection = $data['selected_purchase_price_type'] ?? 'first';

        switch ($priceSelection) {
            case 'first':
                return $data['first_purchase_price'] ?? 0;
            case 'second':
                return $data['second_purchase_price'] ?? 0;
            case 'third':
                return $data['third_purchase_price'] ?? 0;
            default:
                return $data['first_purchase_price'] ?? 0;
        }
    }

    /**
     * ✅ Calculate Final Cost based on formula.
     * Final Cost = (Labor Cost + Operating Cost + Waste Cost + Selected Purchase Price)
     */
    private function calculateFinalCost($data, $selectedPurchasePrice): float
    {
        $laborCost = $data['labor_cost'] ?? 0;
        $operatingCost = $data['operating_cost'] ?? 0;
        $wasteCost = $data['waste_cost'] ?? 0;

        return $laborCost + $operatingCost + $wasteCost + $selectedPurchasePrice;
    }

    /**
     * ✅ Calculate Final Cost for existing formula (API endpoint).
     */
    public function calculateCost(Request $request): JsonResponse
    {
        $laborCost = $request->get('labor_cost', 0);
        $operatingCost = $request->get('operating_cost', 0);
        $wasteCost = $request->get('waste_cost', 0);
        $selectedPurchasePrice = $request->get('selected_purchase_price', 0);

        $finalCost = $laborCost + $operatingCost + $wasteCost + $selectedPurchasePrice;

        return response()->json([
            'success' => true,
            'data' => [
                'labor_cost' => $laborCost,
                'operating_cost' => $operatingCost,
                'waste_cost' => $wasteCost,
                'selected_purchase_price' => $selectedPurchasePrice,
                'final_cost' => $finalCost,
                'formula' => 'Final Cost = Labor Cost + Operating Cost + Waste Cost + Selected Purchase Price'
            ],
            'message' => 'Final cost calculated successfully',
            'message_ar' => 'تم حساب التكلفة النهائية بنجاح'
        ]);
    }

    /**
     * Apply advanced search filters to the query.
     */
    private function applySearchFilters($query, Request $request): void
    {
        // ✅ Manufacturing Formula Number Search
        if ($request->filled('formula_number')) {
            $query->where('formula_number', 'like', '%' . $request->get('formula_number') . '%');
        }

        // ✅ Item Number Search
        if ($request->filled('item_number')) {
            $query->where('item_number', 'like', '%' . $request->get('item_number') . '%');
        }

        // ✅ Item Name Search
        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->get('item_name') . '%');
        }

        // ✅ Manufacturing Duration Search
        if ($request->filled('manufacturing_duration')) {
            $query->where('manufacturing_duration', 'like', '%' . $request->get('manufacturing_duration') . '%');
        }

        // ✅ Produced Quantity Search
        if ($request->filled('produced_quantity')) {
            $query->where('produced_quantity', $request->get('produced_quantity'));
        }

        // ✅ Produced Quantity Range Search
        if ($request->filled('produced_quantity_from')) {
            $query->where('produced_quantity', '>=', $request->get('produced_quantity_from'));
        }
        if ($request->filled('produced_quantity_to')) {
            $query->where('produced_quantity', '<=', $request->get('produced_quantity_to'));
        }

        // ✅ Date Search (Exact Date)
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->get('date'));
        }

        // ✅ Date Range Search (From/To)
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // ✅ Formula Date Search (if using formula_date field)
        if ($request->filled('formula_date')) {
            $query->whereDate('formula_date', $request->get('formula_date'));
        }
        if ($request->filled('formula_date_from')) {
            $query->whereDate('formula_date', '>=', $request->get('formula_date_from'));
        }
        if ($request->filled('formula_date_to')) {
            $query->whereDate('formula_date', '<=', $request->get('formula_date_to'));
        }

        // ✅ Status Search
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // ✅ Active Status Search
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // ✅ Cost Range Searches
        if ($request->filled('labor_cost_from')) {
            $query->where('labor_cost', '>=', $request->get('labor_cost_from'));
        }
        if ($request->filled('labor_cost_to')) {
            $query->where('labor_cost', '<=', $request->get('labor_cost_to'));
        }

        if ($request->filled('total_cost_from')) {
            $query->where('total_production_cost', '>=', $request->get('total_cost_from'));
        }
        if ($request->filled('total_cost_to')) {
            $query->where('total_production_cost', '<=', $request->get('total_cost_to'));
        }

        // ✅ General Search (searches across multiple fields)
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('formula_number', 'like', "%{$searchTerm}%")
                  ->orWhere('formula_name', 'like', "%{$searchTerm}%")
                  ->orWhere('formula_description', 'like', "%{$searchTerm}%")
                  ->orWhere('item_number', 'like', "%{$searchTerm}%")
                  ->orWhere('item_name', 'like', "%{$searchTerm}%")
                  ->orWhere('manufacturing_duration', 'like', "%{$searchTerm}%");
            });
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }

        // Define sortable fields
        $sortableFields = [
            'id', 'formula_number', 'formula_name', 'formula_description',
            'item_number', 'item_name', 'manufacturing_duration',
            'produced_quantity', 'consumed_quantity', 'labor_cost',
            'operating_cost', 'waste_cost', 'total_production_cost',
            'cost_per_unit', 'status', 'is_active', 'effective_from',
            'effective_to', 'created_at', 'updated_at'
        ];

        // Apply sorting if field is sortable
        if (in_array($sortBy, $sortableFields)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }
    }

    /**
     * Get all available fields for dynamic data display.
     */
    public function getAvailableFields(Request $request): JsonResponse
    {
        try {
            $fields = [
                'id' => ['label' => 'ID', 'type' => 'number', 'sortable' => true],
                'formula_number' => ['label' => 'Formula Number', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'formula_name' => ['label' => 'Formula Name', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'formula_description' => ['label' => 'Description', 'type' => 'text', 'sortable' => true, 'searchable' => true],
                'item_number' => ['label' => 'Item Number', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'item_name' => ['label' => 'Item Name', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'manufacturing_duration' => ['label' => 'Manufacturing Duration', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'produced_quantity' => ['label' => 'Produced Quantity', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'consumed_quantity' => ['label' => 'Consumed Quantity', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'labor_cost' => ['label' => 'Labor Cost', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'operating_cost' => ['label' => 'Operating Cost', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'waste_cost' => ['label' => 'Waste Cost', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'total_production_cost' => ['label' => 'Total Production Cost', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'cost_per_unit' => ['label' => 'Cost Per Unit', 'type' => 'number', 'sortable' => true, 'searchable' => true],
                'status' => ['label' => 'Status', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'is_active' => ['label' => 'Active', 'type' => 'boolean', 'sortable' => true, 'searchable' => true],
                'effective_from' => ['label' => 'Effective From', 'type' => 'date', 'sortable' => true, 'searchable' => true],
                'effective_to' => ['label' => 'Effective To', 'type' => 'date', 'sortable' => true, 'searchable' => true],
                'created_at' => ['label' => 'Created Date', 'type' => 'datetime', 'sortable' => true, 'searchable' => true],
                'updated_at' => ['label' => 'Updated Date', 'type' => 'datetime', 'sortable' => true, 'searchable' => true],
                'creator_name' => ['label' => 'Created By', 'type' => 'string', 'sortable' => true, 'searchable' => true],
                'updater_name' => ['label' => 'Updated By', 'type' => 'string', 'sortable' => true, 'searchable' => true],
            ];

            return response()->json([
                'success' => true,
                'data' => $fields,
                'message' => 'Available fields retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field values for dropdown filtering.
     */
    public function getFieldValues(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $field = $request->get('field');

            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field parameter is required'
                ], 400);
            }

            $query = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number');

            // Handle relationship fields
            if (in_array($field, ['item_number', 'item_name'])) {
                $query->with('item');
            } elseif (in_array($field, ['creator_name'])) {
                $query->with('creator');
            } elseif (in_array($field, ['updater_name'])) {
                $query->with('updater');
            }

            $formulas = $query->get();
            $values = collect();

            foreach ($formulas as $formula) {
                $value = $this->getFieldValue($formula, $field);
                if ($value !== null && $value !== '') {
                    $values->push($value);
                }
            }

            $uniqueValues = $values->unique()->filter()->values();

            return response()->json([
                'success' => true,
                'data' => $uniqueValues,
                'message' => 'Field values retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field values: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field value from formula object.
     */
    private function getFieldValue($formula, $field)
    {
        switch ($field) {
            case 'item_number':
                return $formula->item ? $formula->item->item_number : $formula->item_number;
            case 'item_name':
                return $formula->item ? $formula->item->name : $formula->item_name;
            case 'creator_name':
                return $formula->creator ? $formula->creator->name : null;
            case 'updater_name':
                return $formula->updater ? $formula->updater->name : null;
            case 'unit':
                return $formula->item?->unit?->name ?? $formula->unit?->name;
            case 'balance':
                return $formula->item?->balance;
            case 'minimum_limit':
                return $formula->item?->minimum_limit;
            case 'maximum_limit':
                return $formula->item?->maximum_limit;
            case 'reorder_point':
                return $formula->item?->reorder_limit;
            case 'color':
                return $formula->item?->color;
            case 'length':
                return $formula->item?->length;
            case 'width':
                return $formula->item?->width;
            case 'height':
                return $formula->item?->height;
            case 'sale_price':
                return $formula->sale_price;
            case 'purchase_price':
                return $formula->purchase_price;
            default:
                return $formula->{$field} ?? null;
        }
    }

    /**
     * Restore a soft deleted manufacturing formula.
     */
    public function restore(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::withTrashed()
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            if (!$formula->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manufacturing formula is not deleted'
                ], 400);
            }

            $formula->restore();
            $formula->update([
                'deleted_by' => null,
                'deleted_at' => null,
                'updated_by' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ManufacturingFormulaResource($formula->load(['item', 'creator', 'updater'])),
                'message' => 'Manufacturing formula restored successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error restoring manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete a manufacturing formula (permanent deletion).
     */
    public function forceDelete(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::withTrashed()
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            $formula->forceDelete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing formula permanently deleted'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed (soft deleted) manufacturing formulas.
     */
    public function trashed(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $query = BomItem::onlyTrashed()
                ->with(['item', 'creator', 'updater'])
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number');

            // Apply search filters to trashed items too
            $this->applySearchFilters($query, $request);
            $this->applySorting($query, $request);

            $perPage = $request->get('per_page', 15);
            $formulas = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => ManufacturingFormulaResource::collection($formulas->items()),
                'pagination' => [
                    'current_page' => $formulas->currentPage(),
                    'last_page' => $formulas->lastPage(),
                    'per_page' => $formulas->perPage(),
                    'total' => $formulas->total(),
                    'from' => $formulas->firstItem(),
                    'to' => $formulas->lastItem(),
                ],
                'message' => 'Trashed manufacturing formulas retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving trashed manufacturing formulas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed manufacturing formulas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get field-based data display - Show data based on selected field.
     * When user clicks on any field in the table, show related data.
     */
    public function getFieldBasedData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $field = $request->get('field');
            $value = $request->get('value');
            $formulaId = $request->get('formula_id');

            if (!$field) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field parameter is required'
                ], 400);
            }

            // ✅ Get the base formula if formula_id is provided
            $baseFormula = null;
            if ($formulaId) {
                $baseFormula = BomItem::with(['item', 'component', 'unit'])
                    ->where('company_id', $companyId)
                    ->whereNotNull('formula_number')
                    ->find($formulaId);
            }

            // ✅ Build query based on selected field
            $query = BomItem::with([
                'item:id,item_number,name,description,color,length,width,height,balance,minimum_limit,maximum_limit,reorder_limit',
                'component:id,item_number,name,description',
                'unit:id,name,code',
                'creator:id,name',
                'updater:id,name'
            ])
            ->where('company_id', $companyId)
            ->whereNotNull('formula_number');

            // ✅ Apply field-based filtering
            $this->applyFieldBasedFilter($query, $field, $value, $baseFormula);

            // ✅ Get results
            $results = $query->orderBy('created_at', 'desc')->limit(50)->get();

            // ✅ Get field-specific data
            $fieldData = $this->getFieldSpecificData($field, $value, $companyId, $baseFormula);

            // ✅ Format results with enhanced data
            $formattedResults = $results->map(function ($formula) use ($field) {
                return [
                    'id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'formula_name' => $formula->formula_name,

                    // ✅ Item Information (from relationships - no redundant fields)
                    'item_id' => $formula->item_id,
                    'item_number' => $formula->item?->item_number,
                    'item_name' => $formula->item?->name,
                    'item_description' => $formula->item?->description,
                    'unit' => $formula->item?->unit?->name ?? $formula->unit?->name,
                    'balance' => $formula->item?->balance,
                    'minimum_limit' => $formula->item?->minimum_limit,
                    'maximum_limit' => $formula->item?->maximum_limit,
                    'reorder_point' => $formula->item?->reorder_limit,
                    'color' => $formula->item?->color,
                    'length' => $formula->item?->length,
                    'width' => $formula->item?->width,
                    'height' => $formula->item?->height,

                    // ✅ Get sale/purchase prices from manufactured_formulas table
                    'sale_price' => $formula->sale_price,
                    'purchase_price' => $formula->purchase_price,

                    // ✅ Manufacturing Details
                    'manufacturing_duration' => $formula->manufacturing_duration,
                    'consumed_quantity' => $formula->consumed_quantity,
                    'produced_quantity' => $formula->produced_quantity,
                    'formula_date' => $formula->formula_date,
                    'formula_time' => $formula->formula_time,
                    'status' => $formula->status,
                    'is_active' => $formula->is_active,

                    // ✅ Highlight selected field
                    'selected_field' => $field,
                    'selected_field_value' => $this->getFieldValue($formula, $field),
                    'is_match' => $this->isFieldMatch($formula, $field, $value),

                    'created_at' => $formula->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $formula->updated_at?->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'selected_field' => $field,
                    'selected_value' => $value,
                    'base_formula' => $baseFormula ? [
                        'id' => $baseFormula->id,
                        'formula_number' => $baseFormula->formula_number,
                        'formula_name' => $baseFormula->formula_name,
                        'item_name' => $baseFormula->item?->name,
                    ] : null,
                    'field_data' => $fieldData,
                    'formulas' => $formattedResults,
                    'total_results' => $results->count(),
                ],
                'message' => "Data retrieved successfully for field: {$field}"
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving field-based data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving field-based data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get all available fields for selection.
     */
    public function getSelectableFields(): JsonResponse
    {
        $fields = [
            // ✅ Basic Formula Fields
            ['field' => 'formula_number', 'label' => 'Formula Number', 'type' => 'string'],
            ['field' => 'formula_name', 'label' => 'Formula Name', 'type' => 'string'],
            ['field' => 'status', 'label' => 'Status', 'type' => 'enum'],
            ['field' => 'is_active', 'label' => 'Active Status', 'type' => 'boolean'],

            // ✅ Item Fields (from relationships)
            ['field' => 'item_number', 'label' => 'Item Number', 'type' => 'string'],
            ['field' => 'item_name', 'label' => 'Item Name', 'type' => 'string'],
            ['field' => 'unit', 'label' => 'Unit', 'type' => 'string'],
            ['field' => 'balance', 'label' => 'Balance', 'type' => 'decimal'],
            ['field' => 'minimum_limit', 'label' => 'Minimum Limit', 'type' => 'decimal'],
            ['field' => 'maximum_limit', 'label' => 'Maximum Limit', 'type' => 'decimal'],
            ['field' => 'reorder_point', 'label' => 'Reorder Point', 'type' => 'decimal'],
            ['field' => 'color', 'label' => 'Color', 'type' => 'string'],
            ['field' => 'length', 'label' => 'Length', 'type' => 'decimal'],
            ['field' => 'width', 'label' => 'Width', 'type' => 'decimal'],
            ['field' => 'height', 'label' => 'Height', 'type' => 'decimal'],

            // ✅ Price Fields (from Sales/Purchases tables)
            ['field' => 'sale_price', 'label' => 'Sale Price', 'type' => 'decimal'],
            ['field' => 'purchase_price', 'label' => 'Purchase Price', 'type' => 'decimal'],

            // ✅ Manufacturing Fields
            ['field' => 'manufacturing_duration', 'label' => 'Manufacturing Duration', 'type' => 'string'],
            ['field' => 'consumed_quantity', 'label' => 'Consumed Quantity', 'type' => 'decimal'],
            ['field' => 'produced_quantity', 'label' => 'Produced Quantity', 'type' => 'decimal'],
            ['field' => 'formula_date', 'label' => 'Formula Date', 'type' => 'date'],
            ['field' => 'formula_time', 'label' => 'Formula Time', 'type' => 'time'],

            // ✅ System Fields
            ['field' => 'created_at', 'label' => 'Created At', 'type' => 'datetime'],
            ['field' => 'updated_at', 'label' => 'Updated At', 'type' => 'datetime'],
        ];

        return response()->json([
            'success' => true,
            'data' => $fields,
            'message' => 'Selectable fields retrieved successfully'
        ]);
    }

    // ✅ Private Helper Methods for Field-Based Data Display

    /**
     * Apply field-based filtering to query.
     */
    private function applyFieldBasedFilter($query, $field, $value, $baseFormula): void
    {
        if (!$value) {
            return;
        }

        switch ($field) {
            case 'formula_number':
                $query->where('formula_number', 'like', '%' . $value . '%');
                break;
            case 'formula_name':
                $query->where('formula_name', 'like', '%' . $value . '%');
                break;
            case 'status':
                $query->where('status', $value);
                break;
            case 'is_active':
                $query->where('is_active', $value);
                break;
            case 'item_number':
                $query->whereHas('item', function ($q) use ($value) {
                    $q->where('item_number', 'like', '%' . $value . '%');
                });
                break;
            case 'item_name':
                $query->whereHas('item', function ($q) use ($value) {
                    $q->where('name', 'like', '%' . $value . '%');
                });
                break;
            case 'color':
                $query->whereHas('item', function ($q) use ($value) {
                    $q->where('color', 'like', '%' . $value . '%');
                });
                break;
            case 'manufacturing_duration':
                $query->where('manufacturing_duration', 'like', '%' . $value . '%');
                break;
            case 'formula_date':
                $query->whereDate('formula_date', $value);
                break;
            default:
                // For numeric fields
                if (is_numeric($value)) {
                    $query->where($field, $value);
                }
                break;
        }
    }

    /**
     * Get field-specific data and statistics.
     */
    private function getFieldSpecificData($field, $value, $companyId, $baseFormula): array
    {
        $data = [
            'field' => $field,
            'value' => $value,
            'statistics' => [],
            'related_data' => [],
        ];

        try {
            switch ($field) {
                case 'item_number':
                case 'item_name':
                    $data['related_data'] = $this->getItemRelatedData($value, $companyId);
                    break;
                case 'color':
                    $data['related_data'] = $this->getColorRelatedData($value, $companyId);
                    break;
                case 'status':
                    $data['statistics'] = $this->getStatusStatistics($companyId);
                    break;
                case 'manufacturing_duration':
                    $data['statistics'] = $this->getDurationStatistics($companyId);
                    break;
                case 'formula_date':
                    $data['statistics'] = $this->getDateStatistics($companyId);
                    break;
                default:
                    $data['statistics'] = $this->getGeneralFieldStatistics($field, $companyId);
                    break;
            }
        } catch (\Exception $e) {
            Log::warning("Error getting field-specific data for {$field}: " . $e->getMessage());
        }

        return $data;
    }

    /**
     * ✅ Get sale price from Suppliers table.
     */
    private function getLatestSalePrice($itemId): ?float
    {
        try {
            // Get sale price from suppliers table for this item
            $salePrice = DB::table('suppliers')
                ->join('items', 'suppliers.item_id', '=', 'items.id')
                ->where('suppliers.item_id', $itemId)
                ->where('suppliers.status', 'active')
                ->orderBy('suppliers.updated_at', 'desc')
                ->value('suppliers.selling_price');

            return $salePrice ? (float) $salePrice : null;
        } catch (\Exception $e) {
            Log::warning("Error getting sale price from suppliers for item {$itemId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ Get purchase price from Suppliers table.
     */
    private function getLatestPurchasePrice($itemId): ?float
    {
        try {
            // Get purchase price from suppliers table for this item
            $purchasePrice = DB::table('suppliers')
                ->join('items', 'suppliers.item_id', '=', 'items.id')
                ->where('suppliers.item_id', $itemId)
                ->where('suppliers.status', 'active')
                ->orderBy('suppliers.updated_at', 'desc')
                ->value('suppliers.purchase_price');

            return $purchasePrice ? (float) $purchasePrice : null;
        } catch (\Exception $e) {
            Log::warning("Error getting purchase price from suppliers for item {$itemId}: " . $e->getMessage());
            return null;
        }
    }



    /**
     * Check if field matches the given value.
     */
    private function isFieldMatch($formula, $field, $value): bool
    {
        if (!$value) {
            return true;
        }

        $fieldValue = $this->getFieldValue($formula, $field);

        if (is_string($fieldValue)) {
            return stripos($fieldValue, $value) !== false;
        }

        return $fieldValue == $value;
    }

    /**
     * Get item-related data.
     */
    private function getItemRelatedData($value, $companyId): array
    {
        try {
            $items = Item::where('company_id', $companyId)
                ->where(function ($q) use ($value) {
                    $q->where('item_number', 'like', '%' . $value . '%')
                      ->orWhere('name', 'like', '%' . $value . '%');
                })
                ->limit(10)
                ->get(['id', 'item_number', 'name', 'description', 'color', 'balance']);

            return [
                'items' => $items->toArray(),
                'total_items' => $items->count(),
            ];
        } catch (\Exception $e) {
            return ['items' => [], 'total_items' => 0];
        }
    }

    /**
     * Get color-related data.
     */
    private function getColorRelatedData($value, $companyId): array
    {
        try {
            $colors = Item::where('company_id', $companyId)
                ->where('color', 'like', '%' . $value . '%')
                ->distinct()
                ->pluck('color')
                ->filter()
                ->take(10);

            return [
                'colors' => $colors->toArray(),
                'total_colors' => $colors->count(),
            ];
        } catch (\Exception $e) {
            return ['colors' => [], 'total_colors' => 0];
        }
    }

    /**
     * Get status statistics.
     */
    private function getStatusStatistics($companyId): array
    {
        try {
            $stats = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'status_distribution' => $stats,
                'total_formulas' => array_sum($stats),
            ];
        } catch (\Exception $e) {
            return ['status_distribution' => [], 'total_formulas' => 0];
        }
    }

    /**
     * Get duration statistics.
     */
    private function getDurationStatistics($companyId): array
    {
        try {
            $durations = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->whereNotNull('manufacturing_duration')
                ->pluck('manufacturing_duration')
                ->countBy()
                ->toArray();

            return [
                'duration_distribution' => $durations,
                'unique_durations' => count($durations),
            ];
        } catch (\Exception $e) {
            return ['duration_distribution' => [], 'unique_durations' => 0];
        }
    }

    /**
     * Get date statistics.
     */
    private function getDateStatistics($companyId): array
    {
        try {
            $dateStats = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->whereNotNull('formula_date')
                ->selectRaw('DATE(formula_date) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(10)
                ->pluck('count', 'date')
                ->toArray();

            return [
                'date_distribution' => $dateStats,
                'total_dates' => count($dateStats),
            ];
        } catch (\Exception $e) {
            return ['date_distribution' => [], 'total_dates' => 0];
        }
    }

    /**
     * Get general field statistics.
     */
    private function getGeneralFieldStatistics($field, $companyId): array
    {
        try {
            $stats = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->whereNotNull($field)
                ->selectRaw("COUNT(*) as total, AVG({$field}) as average, MIN({$field}) as minimum, MAX({$field}) as maximum")
                ->first();

            return [
                'total' => $stats->total ?? 0,
                'average' => $stats->average ? round($stats->average, 2) : 0,
                'minimum' => $stats->minimum ?? 0,
                'maximum' => $stats->maximum ?? 0,
            ];
        } catch (\Exception $e) {
            return ['total' => 0, 'average' => 0, 'minimum' => 0, 'maximum' => 0];
        }
    }

    /**
     * ✅ Get manufacturing formula numbers for dropdown.
     */
    public function getManufacturingFormulaNumbers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $formulas = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->select('id', 'formula_number', 'formula_name', 'item_id')
                ->orderBy('formula_number')
                ->get();

            $formulaOptions = $formulas->map(function ($formula) {
                return [
                    'id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'formula_name' => $formula->formula_name,
                    'item_id' => $formula->item_id,
                    'display_text' => $formula->formula_number . ($formula->formula_name ? ' - ' . $formula->formula_name : '')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formulaOptions,
                'message' => 'Manufacturing formula numbers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving manufacturing formula numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get item details by manufacturing formula number.
     */
    public function getItemByFormulaNumber(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $formulaId = $request->get('formula_id');

            if (!$formulaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formula ID is required'
                ], 400);
            }

            $formula = BomItem::with(['item.unit'])
                ->where('company_id', $companyId)
                ->findOrFail($formulaId);

            $itemDetails = null;
            if ($formula->item) {
                $itemDetails = [
                    'item_id' => $formula->item->id,
                    'item_number' => $formula->item->item_number,
                    'item_name' => $formula->item->name,
                    'description' => $formula->item->description,
                    'unit_id' => $formula->item->unit_id,
                    'unit_name' => $formula->item->unit?->name,
                    'balance' => $formula->item->balance,
                    'color' => $formula->item->color,
                    'length' => $formula->item->length,
                    'width' => $formula->item->width,
                    'height' => $formula->item->height,
                    'sale_price' => $formula->sale_price,
                    'purchase_price' => $formula->purchase_price,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'formula' => [
                        'id' => $formula->id,
                        'formula_number' => $formula->formula_number,
                        'formula_name' => $formula->formula_name,
                        'produced_quantity' => $formula->produced_quantity,
                    ],
                    'item' => $itemDetails
                ],
                'message' => 'Item details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving item details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get warehouses for dropdown.
     */
    public function getWarehouses(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $warehouses = \Modules\Inventory\Models\Warehouse::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'warehouse_number', 'name', 'address')
                ->orderBy('warehouse_number')
                ->get();

            $warehouseOptions = $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'warehouse_number' => $warehouse->warehouse_number,
                    'name' => $warehouse->name,
                    'address' => $warehouse->address,
                    'display_text' => $warehouse->warehouse_number . ' - ' . $warehouse->name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $warehouseOptions,
                'message' => 'Warehouses retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving warehouses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Update prices from suppliers table for a manufactured formula.
     */
    public function updatePricesFromSuppliers(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $formula = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->findOrFail($id);

            // Get prices from suppliers table
            $salePrice = $this->getLatestSalePrice($formula->item_id);
            $purchasePrice = $this->getLatestPurchasePrice($formula->item_id);

            // Update the manufactured formula with new prices
            $formula->update([
                'sale_price' => $salePrice ?? 0,
                'purchase_price' => $purchasePrice ?? 0,
                'updated_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'formula_id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'item_name' => $formula->item?->name,
                    'sale_price' => $formula->sale_price,
                    'purchase_price' => $formula->purchase_price,
                    'updated_at' => $formula->updated_at->format('Y-m-d H:i:s'),
                ],
                'message' => 'Prices updated successfully from suppliers table'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating prices from suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating prices from suppliers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Update prices from suppliers table for all manufactured formulas.
     */
    public function updateAllPricesFromSuppliers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $formulas = BomItem::where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->with('item')
                ->get();

            $updatedCount = 0;
            $errors = [];

            foreach ($formulas as $formula) {
                try {
                    // Get prices from suppliers table
                    $salePrice = $this->getLatestSalePrice($formula->item_id);
                    $purchasePrice = $this->getLatestPurchasePrice($formula->item_id);

                    // Update the formula with new prices
                    $formula->update([
                        'sale_price' => $salePrice ?? 0,
                        'purchase_price' => $purchasePrice ?? 0,
                        'updated_by' => $user->id,
                    ]);

                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = [
                        'formula_id' => $formula->id,
                        'formula_number' => $formula->formula_number,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_formulas' => $formulas->count(),
                    'updated_count' => $updatedCount,
                    'errors_count' => count($errors),
                    'errors' => $errors,
                ],
                'message' => "Successfully updated prices for {$updatedCount} manufactured formulas"
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating all prices from suppliers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating all prices from suppliers: ' . $e->getMessage()
            ], 500);
        }
    }
}
