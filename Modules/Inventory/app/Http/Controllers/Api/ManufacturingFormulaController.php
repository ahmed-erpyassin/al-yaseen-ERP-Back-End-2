<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Unit;
use Modules\Inventory\Http\Requests\StoreManufacturingFormulaRequest;
use Illuminate\Support\Facades\DB;

class ManufacturingFormulaController extends Controller
{
    /**
     * ✅ Get all Item Numbers for dropdown simulation.
     */
    public function getItemNumbers(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id ?? $request->company_id;
        
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
        $companyId = auth()->user()->company_id ?? $request->company_id;
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
        $companyId = auth()->user()->company_id ?? $request->company_id;
        $userId = auth()->id() ?? $request->user_id;

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
}
