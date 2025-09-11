<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryMovementData;

class AddManufacturingFormulaController extends Controller
{
    /**
     * Get existing manufacturing formula numbers for dropdown.
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
     * Get item details automatically after selecting manufacturing formula number.
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

            $formula = BomItem::with(['item'])
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
                    'unit_name' => $formula->item->unit_name,
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
     * Get all item numbers for dropdown (based on manufacturing formula).
     */
    public function getItemNumbers(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $formulaId = $request->get('formula_id');

            $query = Item::where('company_id', $companyId);

            // If formula_id is provided, get items related to that formula
            if ($formulaId) {
                $formula = BomItem::find($formulaId);
                if ($formula) {
                    // Get the specific item for this formula
                    $query->where('id', $formula->item_id);
                }
            }

            $items = $query->select('id', 'item_number', 'name', 'description', 'unit_id', 'unit_name')
                ->orderBy('item_number')
                ->get();

            $itemOptions = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'description' => $item->description,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit_name,
                    'display_text' => $item->item_number . ' - ' . $item->name
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $itemOptions,
                'message' => 'Item numbers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving item numbers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all item names for dropdown (bidirectional linking).
     */
    public function getItemNames(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $formulaId = $request->get('formula_id');

            $query = Item::where('company_id', $companyId);

            // If formula_id is provided, get items related to that formula
            if ($formulaId) {
                $formula = BomItem::find($formulaId);
                if ($formula) {
                    // Get the specific item for this formula
                    $query->where('id', $formula->item_id);
                }
            }

            $items = $query->select('id', 'item_number', 'name', 'description', 'unit_id', 'unit_name')
                ->orderBy('name')
                ->get();

            $itemOptions = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'description' => $item->description,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit_name,
                    'display_text' => $item->name . ' - ' . $item->item_number
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $itemOptions,
                'message' => 'Item names retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving item names: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item details by item number or name (bidirectional linking).
     */
    public function getItemDetails(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $itemId = $request->get('item_id');
            $itemNumber = $request->get('item_number');
            $itemName = $request->get('item_name');

            $query = Item::where('company_id', $companyId);

            if ($itemId) {
                $query->where('id', $itemId);
            } elseif ($itemNumber) {
                $query->where('item_number', $itemNumber);
            } elseif ($itemName) {
                $query->where('name', 'like', "%{$itemName}%");
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Item ID, number, or name is required'
                ], 400);
            }

            $item = $query->first();

            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found'
                ], 404);
            }

            $itemDetails = [
                'item_id' => $item->id,
                'item_number' => $item->item_number,
                'item_name' => $item->name,
                'description' => $item->description,
                'unit_id' => $item->unit_id,
                'unit_name' => $item->unit_name,
            ];

            return response()->json([
                'success' => true,
                'data' => $itemDetails,
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
     * Get warehouses for dropdown.
     */
    public function getWarehouses(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $warehouses = Warehouse::where('company_id', $companyId)
                ->where('status', 'active')
                ->select('id', 'warehouse_number', 'name', 'address')
                ->orderBy('warehouse_number')
                ->get();

            $warehouseOptions = $warehouses->map(function ($warehouse) {
                return [
                    'id' => $warehouse->id,
                    'warehouse_number' => $warehouse->warehouse_number,
                    'warehouse_name' => $warehouse->name,
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
     * Get raw materials for a specific manufacturing formula.
     */
    public function getRawMaterialsByFormula(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $formulaId = $request->get('formula_id');
            $warehouseId = $request->get('warehouse_id');

            if (!$formulaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formula ID is required'
                ], 400);
            }

            // Get formula components (raw materials)
            $components = BomItem::with(['item'])
                ->where('company_id', $companyId)
                ->where('parent_id', $formulaId) // Components of this formula
                ->orWhere(function($query) use ($formulaId, $companyId) {
                    $query->where('id', $formulaId)
                          ->where('company_id', $companyId)
                          ->whereNotNull('component_id'); // Or the formula itself if it has component data
                })
                ->get();

            $rawMaterials = [];

            foreach ($components as $component) {
                $item = $component->item ?? ($component->component_id ? Item::find($component->component_id) : null);

                if (!$item) continue;

                $availableQuantity = 0;
                $isAvailable = false;

                // Get available quantity from warehouse if specified
                if ($warehouseId) {
                    $stock = InventoryStock::where('inventory_item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    $availableQuantity = $stock ? $stock->available_quantity : 0;
                    $isAvailable = $availableQuantity >= ($component->consumed_quantity ?? 0);
                }

                $rawMaterials[] = [
                    'item_id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'description' => $item->description,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit_name,
                    'consumed_quantity' => $component->consumed_quantity ?? 0,
                    'available_quantity' => $availableQuantity,
                    'unit_cost' => $item->cost_price ?? 0,
                    'is_available' => $isAvailable,
                    'shortage_quantity' => max(0, ($component->consumed_quantity ?? 0) - $availableQuantity),
                    'can_select' => $isAvailable, // Can only select if available
                    'display_text' => $item->item_number . ' - ' . $item->name,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $rawMaterials,
                'message' => 'Raw materials retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving raw materials: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available raw material items for dropdown (based on formula).
     */
    public function getRawMaterialItems(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $formulaId = $request->get('formula_id');
            $warehouseId = $request->get('warehouse_id');

            if (!$formulaId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formula ID is required'
                ], 400);
            }

            // Get formula components (raw materials)
            $components = BomItem::with(['item'])
                ->where('company_id', $companyId)
                ->where('parent_id', $formulaId)
                ->get();

            $rawMaterialItems = [];

            foreach ($components as $component) {
                $item = $component->item ?? ($component->component_id ? Item::find($component->component_id) : null);

                if (!$item) continue;

                $availableQuantity = 0;
                $isAvailable = false;

                // Get available quantity from warehouse if specified
                if ($warehouseId) {
                    $stock = InventoryStock::where('inventory_item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    $availableQuantity = $stock ? $stock->available_quantity : 0;
                    $isAvailable = $availableQuantity > 0;
                }

                // Only include items that are available or show all with availability status
                $rawMaterialItems[] = [
                    'item_id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'description' => $item->description,
                    'unit_id' => $item->unit_id,
                    'unit_name' => $item->unit_name,
                    'available_quantity' => $availableQuantity,
                    'unit_cost' => $item->cost_price ?? 0,
                    'is_available' => $isAvailable,
                    'can_select' => $isAvailable,
                    'display_text' => $item->item_number . ' - ' . $item->name .
                                    ($isAvailable ? ' (Available: ' . $availableQuantity . ')' : ' (Not Available)'),
                    'status_text' => $isAvailable ? 'Available' : 'Missing Materials',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $rawMaterialItems,
                'message' => 'Raw material items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving raw material items: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check raw material availability for a specific item and quantity.
     */
    public function checkRawMaterialAvailability(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;
            $itemId = $request->get('item_id');
            $warehouseId = $request->get('warehouse_id');
            $requiredQuantity = $request->get('required_quantity', 0);

            if (!$itemId || !$warehouseId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item ID and Warehouse ID are required'
                ], 400);
            }

            $item = Item::where('company_id', $companyId)->findOrFail($itemId);

            $stock = InventoryStock::where('inventory_item_id', $itemId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $availableQuantity = $stock ? $stock->available_quantity : 0;
            $isAvailable = $availableQuantity >= $requiredQuantity;
            $shortageQuantity = max(0, $requiredQuantity - $availableQuantity);

            return response()->json([
                'success' => true,
                'data' => [
                    'item_id' => $item->id,
                    'item_number' => $item->item_number,
                    'item_name' => $item->name,
                    'required_quantity' => $requiredQuantity,
                    'available_quantity' => $availableQuantity,
                    'is_available' => $isAvailable,
                    'shortage_quantity' => $shortageQuantity,
                    'can_select' => $isAvailable,
                    'status' => $isAvailable ? 'Available' : 'Insufficient',
                    'message' => $isAvailable ?
                        'Material is available' :
                        'Insufficient materials - shortage of ' . $shortageQuantity . ' units'
                ],
                'message' => 'Availability check completed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new manufacturing formula with calculation.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            // Validate the request
            $validatedData = $this->validateStoreRequest($request);

            // Create the manufacturing formula
            $formulaData = [
                'user_id' => $user->id,
                'company_id' => $companyId,
                'item_id' => $validatedData['item_id'],
                'formula_number' => $this->generateFormulaNumber($companyId),
                'formula_name' => $validatedData['formula_name'] ?? null,
                'formula_description' => $validatedData['formula_description'] ?? null,
                'manufacturing_duration' => $validatedData['manufacturing_duration'] ?? null,
                'manufacturing_duration_unit' => $validatedData['manufacturing_duration_unit'] ?? 'days',
                'produced_quantity' => $validatedData['produced_quantity'],
                'consumed_quantity' => 0, // Will be calculated from raw materials
                'status' => 'active',
                'is_active' => true,
                'created_by' => $user->id,
            ];

            // Get item details
            $item = Item::findOrFail($validatedData['item_id']);
            $formulaData['item_number'] = $item->item_number;
            $formulaData['item_name'] = $item->name;

            // Create the main formula record
            $formula = BomItem::create($formulaData);

            // Add raw materials as components
            $totalRawMaterialCost = 0;
            if (isset($validatedData['raw_materials']) && is_array($validatedData['raw_materials'])) {
                foreach ($validatedData['raw_materials'] as $rawMaterial) {
                    $this->addRawMaterialComponent($formula, $rawMaterial, $companyId, $user->id);
                    $totalRawMaterialCost += ($rawMaterial['consumed_quantity'] * $rawMaterial['unit_cost']);
                }
            }

            // Update formula with calculated costs
            $formula->update([
                'material_cost' => $totalRawMaterialCost,
                'total_production_cost' => $totalRawMaterialCost + ($validatedData['labor_cost'] ?? 0) + ($validatedData['overhead_cost'] ?? 0),
                'cost_per_unit' => $validatedData['produced_quantity'] > 0 ?
                    ($totalRawMaterialCost + ($validatedData['labor_cost'] ?? 0) + ($validatedData['overhead_cost'] ?? 0)) / $validatedData['produced_quantity'] : 0,
            ]);

            DB::commit();

            // Load relationships for response
            $formula->load(['item', 'creator']);

            return response()->json([
                'success' => true,
                'data' => [
                    'formula' => [
                        'id' => $formula->id,
                        'formula_number' => $formula->formula_number,
                        'formula_name' => $formula->formula_name,
                        'item_id' => $formula->item_id,
                        'item_number' => $formula->item_number,
                        'item_name' => $formula->item_name,
                        'produced_quantity' => $formula->produced_quantity,
                        'manufacturing_duration' => $formula->manufacturing_duration,
                        'manufacturing_duration_unit' => $formula->manufacturing_duration_unit,
                        'total_production_cost' => $formula->total_production_cost,
                        'cost_per_unit' => $formula->cost_per_unit,
                        'status' => $formula->status,
                    ]
                ],
                'message' => 'Manufacturing formula created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate manufacturing formula - deduct raw materials and add finished product.
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $formulaId = $request->get('formula_id');
            $rawMaterialsWarehouseId = $request->get('raw_materials_warehouse_id');
            $finishedProductWarehouseId = $request->get('finished_product_warehouse_id');
            $producedQuantity = $request->get('produced_quantity');

            if (!$formulaId || !$rawMaterialsWarehouseId || !$finishedProductWarehouseId || !$producedQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formula ID, warehouses, and produced quantity are required'
                ], 400);
            }

            $formula = BomItem::with(['item'])->where('company_id', $companyId)->findOrFail($formulaId);

            // Check raw material availability
            $availabilityCheck = $this->checkAllRawMaterialsAvailability($formulaId, $rawMaterialsWarehouseId, $producedQuantity);

            if (!$availabilityCheck['all_available']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient raw materials',
                    'missing_materials' => $availabilityCheck['missing_materials']
                ], 422);
            }

            // Deduct raw materials from warehouse
            $totalRawMaterialCost = $this->deductRawMaterials($formulaId, $rawMaterialsWarehouseId, $producedQuantity, $user->id);

            // Add finished product to warehouse
            $this->addFinishedProduct($formula, $finishedProductWarehouseId, $producedQuantity, $user->id);

            // Create inventory movements
            $this->createManufacturingMovements($formula, $rawMaterialsWarehouseId, $finishedProductWarehouseId, $producedQuantity, $totalRawMaterialCost, $user->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'formula_id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'produced_quantity' => $producedQuantity,
                    'total_raw_material_cost' => $totalRawMaterialCost,
                    'cost_per_unit' => $producedQuantity > 0 ? $totalRawMaterialCost / $producedQuantity : 0,
                ],
                'message' => 'Manufacturing formula calculated successfully',
                'calculation_summary' => [
                    'raw_materials_deducted' => true,
                    'finished_products_added' => $producedQuantity,
                    'total_cost' => $totalRawMaterialCost,
                    'cost_per_unit' => $producedQuantity > 0 ? $totalRawMaterialCost / $producedQuantity : 0,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calculating manufacturing formula: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error calculating manufacturing formula: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate store request data.
     */
    private function validateStoreRequest(Request $request): array
    {
        return $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'formula_name' => 'nullable|string|max:255',
            'formula_description' => 'nullable|string|max:1000',
            'manufacturing_duration' => 'nullable|string|max:255',
            'manufacturing_duration_unit' => 'nullable|in:minutes,hours,days,weeks,months',
            'produced_quantity' => 'required|numeric|min:0.0001',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            'raw_materials' => 'required|array|min:1',
            'raw_materials.*.item_id' => 'required|integer|exists:items,id',
            'raw_materials.*.consumed_quantity' => 'required|numeric|min:0.0001',
            'raw_materials.*.unit_cost' => 'required|numeric|min:0',
        ]);
    }

    /**
     * Generate unique formula number.
     */
    private function generateFormulaNumber(int $companyId): string
    {
        $prefix = 'MF-';
        $lastFormula = BomItem::where('company_id', $companyId)
            ->whereNotNull('formula_number')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastFormula && $lastFormula->formula_number) {
            $lastNumber = (int) str_replace($prefix, '', $lastFormula->formula_number);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Add raw material component to formula.
     */
    private function addRawMaterialComponent(BomItem $formula, array $rawMaterial, int $companyId, int $userId): void
    {
        $item = Item::find($rawMaterial['item_id']);
        if (!$item) return;

        $componentData = [
            'user_id' => $userId,
            'company_id' => $companyId,
            'item_id' => $formula->item_id, // Parent item
            'component_id' => $rawMaterial['item_id'], // Raw material item
            'parent_id' => $formula->id, // Link to parent formula
            'consumed_quantity' => $rawMaterial['consumed_quantity'],
            'unit_cost' => $rawMaterial['unit_cost'],
            'total_cost' => $rawMaterial['consumed_quantity'] * $rawMaterial['unit_cost'],
            'component_type' => 'raw_material',
            'is_active' => true,
            'status' => 'active',
            'created_by' => $userId,
        ];

        BomItem::create($componentData);
    }

    /**
     * Check availability of all raw materials.
     */
    private function checkAllRawMaterialsAvailability(int $formulaId, int $warehouseId, float $producedQuantity): array
    {
        $components = BomItem::where('parent_id', $formulaId)->get();
        $missingMaterials = [];
        $allAvailable = true;

        foreach ($components as $component) {
            $requiredQuantity = $component->consumed_quantity * $producedQuantity;

            $stock = InventoryStock::where('inventory_item_id', $component->component_id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $availableQuantity = $stock ? $stock->available_quantity : 0;

            if ($availableQuantity < $requiredQuantity) {
                $allAvailable = false;
                $item = Item::find($component->component_id);

                $missingMaterials[] = [
                    'item_id' => $component->component_id,
                    'item_number' => $item ? $item->item_number : null,
                    'item_name' => $item ? $item->name : null,
                    'required_quantity' => $requiredQuantity,
                    'available_quantity' => $availableQuantity,
                    'shortage_quantity' => $requiredQuantity - $availableQuantity,
                ];
            }
        }

        return [
            'all_available' => $allAvailable,
            'missing_materials' => $missingMaterials,
        ];
    }

    /**
     * Deduct raw materials from warehouse.
     */
    private function deductRawMaterials(int $formulaId, int $warehouseId, float $producedQuantity, int $userId): float
    {
        $components = BomItem::where('parent_id', $formulaId)->get();
        $totalCost = 0;

        foreach ($components as $component) {
            $requiredQuantity = $component->consumed_quantity * $producedQuantity;

            $stock = InventoryStock::where('inventory_item_id', $component->component_id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            if ($stock && $stock->available_quantity >= $requiredQuantity) {
                $stock->quantity -= $requiredQuantity;
                $stock->available_quantity -= $requiredQuantity;
                $stock->save();

                $totalCost += $requiredQuantity * $component->unit_cost;
            }
        }

        return $totalCost;
    }

    /**
     * Add finished product to warehouse.
     */
    private function addFinishedProduct(BomItem $formula, int $warehouseId, float $producedQuantity, int $userId): void
    {
        $stock = InventoryStock::firstOrCreate([
            'inventory_item_id' => $formula->item_id,
            'warehouse_id' => $warehouseId,
            'company_id' => $formula->company_id,
        ], [
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
            'created_by' => $userId,
        ]);

        $stock->quantity += $producedQuantity;
        $stock->available_quantity += $producedQuantity;
        $stock->save();
    }

    /**
     * Create inventory movements for manufacturing.
     */
    private function createManufacturingMovements(BomItem $formula, int $rawWarehouseId, int $finishedWarehouseId, float $producedQuantity, float $totalCost, int $userId): void
    {
        // Create outbound movement for raw materials
        $outboundMovement = InventoryMovement::create([
            'company_id' => $formula->company_id,
            'user_id' => $userId,
            'movement_number' => 'MFG-OUT-' . $formula->id . '-' . time(),
            'movement_type' => 'manufacturing',
            'movement_date' => now()->toDateString(),
            'movement_datetime' => now(),
            'warehouse_id' => $rawWarehouseId,
            'movement_description' => 'Raw materials consumed for manufacturing formula ' . $formula->formula_number,
            'status' => 'confirmed',
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'total_value' => $totalCost,
            'created_by' => $userId,
        ]);

        // Create inbound movement for finished product
        $inboundMovement = InventoryMovement::create([
            'company_id' => $formula->company_id,
            'user_id' => $userId,
            'movement_number' => 'MFG-IN-' . $formula->id . '-' . time(),
            'movement_type' => 'manufacturing',
            'movement_date' => now()->toDateString(),
            'movement_datetime' => now(),
            'warehouse_id' => $finishedWarehouseId,
            'movement_description' => 'Finished product from manufacturing formula ' . $formula->formula_number,
            'status' => 'confirmed',
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'total_quantity' => $producedQuantity,
            'total_value' => $totalCost,
            'created_by' => $userId,
        ]);
    }
}
