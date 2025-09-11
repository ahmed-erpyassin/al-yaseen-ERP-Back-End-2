<?php

namespace Modules\Inventory\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\ManufacturingProcess;
use Modules\Inventory\Models\ManufacturingProcessRawMaterial;
use Modules\Inventory\Models\BomItem;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Models\InventoryStock;
use Modules\Inventory\Http\Requests\StoreManufacturingProcessRequest;
use Modules\Inventory\Http\Requests\UpdateManufacturingProcessRequest;
use Modules\Inventory\Http\Resources\ManufacturingProcessResource;

class ManufacturingProcessController extends Controller
{
    /**
     * Display a listing of manufacturing processes.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $query = ManufacturingProcess::with([
                'manufacturingFormula',
                'item',
                'rawMaterialsWarehouse',
                'finishedProductWarehouse',
                'rawMaterials',
                'creator'
            ])->forCompany($companyId);

            // Apply filters
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('formula_id')) {
                $query->byFormula($request->formula_id);
            }

            $perPage = $request->get('per_page', 15);
            $processes = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Transform using resource
            $processes->getCollection()->transform(function ($process) {
                return new ManufacturingProcessResource($process);
            });

            return response()->json([
                'success' => true,
                'data' => $processes,
                'message' => 'Manufacturing processes retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving manufacturing processes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created manufacturing process.
     */
    public function store(StoreManufacturingProcessRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;
            $data = $request->validated();

            // Add system fields
            $data['user_id'] = $user->id;
            $data['company_id'] = $companyId;
            $data['created_by'] = $user->id;
            $data['process_date'] = now()->toDateString();

            // Get manufacturing formula details
            if (isset($data['manufacturing_formula_id'])) {
                $formula = BomItem::find($data['manufacturing_formula_id']);
                if ($formula) {
                    $data['manufacturing_formula_number'] = $formula->formula_number;
                    $data['manufacturing_formula_name'] = $formula->formula_name;
                }
            }

            // Get item details
            if (isset($data['item_id'])) {
                $item = Item::find($data['item_id']);
                if ($item) {
                    $data['item_number'] = $item->item_number;
                    $data['item_name'] = $item->name;
                }
            }

            // Get warehouse details
            if (isset($data['raw_materials_warehouse_id'])) {
                $warehouse = Warehouse::find($data['raw_materials_warehouse_id']);
                if ($warehouse) {
                    $data['raw_materials_warehouse_name'] = $warehouse->name;
                }
            }

            if (isset($data['finished_product_warehouse_id'])) {
                $warehouse = Warehouse::find($data['finished_product_warehouse_id']);
                if ($warehouse) {
                    $data['finished_product_warehouse_name'] = $warehouse->name;
                }
            }

            // Set default values
            $data['status'] = $data['status'] ?? 'draft';
            $data['expected_quantity'] = $data['produced_quantity'];

            // Create manufacturing process
            $process = ManufacturingProcess::create($data);

            // Add raw materials if provided
            if (isset($data['raw_materials']) && is_array($data['raw_materials'])) {
                $this->addRawMaterials($process, $data['raw_materials'], $companyId, $user->id);
            }

            // Load relationships
            $process->load([
                'manufacturingFormula',
                'item',
                'rawMaterialsWarehouse',
                'finishedProductWarehouse',
                'rawMaterials.item',
                'creator'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ManufacturingProcessResource($process),
                'message' => 'Manufacturing process created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating manufacturing process: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating manufacturing process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified manufacturing process.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = request()->user();
            $companyId = $user->company_id;

            $process = ManufacturingProcess::with([
                'manufacturingFormula',
                'item',
                'rawMaterialsWarehouse',
                'finishedProductWarehouse',
                'rawMaterials.item',
                'rawMaterials.warehouse',
                'creator',
                'updater'
            ])->forCompany($companyId)->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => new ManufacturingProcessResource($process),
                'message' => 'Manufacturing process retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Manufacturing process not found or does not belong to your company'
            ], 404);
        }
    }

    /**
     * Update the specified manufacturing process.
     */
    public function update(UpdateManufacturingProcessRequest $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $process = ManufacturingProcess::forCompany($companyId)->findOrFail($id);
            $data = $request->validated();

            // Add system fields
            $data['updated_by'] = $user->id;

            // Update item details if item_id changed
            if (isset($data['item_id']) && $data['item_id'] !== $process->item_id) {
                $item = Item::find($data['item_id']);
                if ($item) {
                    $data['item_number'] = $item->item_number;
                    $data['item_name'] = $item->name;
                }
            }

            // Update warehouse details if changed
            if (isset($data['raw_materials_warehouse_id']) && $data['raw_materials_warehouse_id'] !== $process->raw_materials_warehouse_id) {
                $warehouse = Warehouse::find($data['raw_materials_warehouse_id']);
                if ($warehouse) {
                    $data['raw_materials_warehouse_name'] = $warehouse->name;
                }
            }

            if (isset($data['finished_product_warehouse_id']) && $data['finished_product_warehouse_id'] !== $process->finished_product_warehouse_id) {
                $warehouse = Warehouse::find($data['finished_product_warehouse_id']);
                if ($warehouse) {
                    $data['finished_product_warehouse_name'] = $warehouse->name;
                }
            }

            // Update the process
            $process->update($data);

            // Update raw materials if provided
            if (isset($data['raw_materials']) && is_array($data['raw_materials'])) {
                // Remove existing raw materials
                $process->rawMaterials()->delete();
                // Add new raw materials
                $this->addRawMaterials($process, $data['raw_materials'], $companyId, $user->id);
            }

            // Reload relationships
            $process->load([
                'manufacturingFormula',
                'item',
                'rawMaterialsWarehouse',
                'finishedProductWarehouse',
                'rawMaterials.item',
                'creator',
                'updater'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => new ManufacturingProcessResource($process),
                'message' => 'Manufacturing process updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating manufacturing process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified manufacturing process.
     */
    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = request()->user();
            $companyId = $user->company_id;

            $process = ManufacturingProcess::forCompany($companyId)->findOrFail($id);

            // Check if process can be deleted
            if ($process->status === 'in_progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete a manufacturing process that is in progress'
                ], 422);
            }

            $process->update(['deleted_by' => $user->id]);
            $process->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Manufacturing process deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting manufacturing process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add raw materials to manufacturing process.
     */
    private function addRawMaterials(ManufacturingProcess $process, array $rawMaterials, int $companyId, int $userId): void
    {
        foreach ($rawMaterials as $rawMaterial) {
            $item = Item::find($rawMaterial['item_id']);
            if (!$item) continue;

            $rawMaterialData = [
                'manufacturing_process_id' => $process->id,
                'company_id' => $companyId,
                'item_id' => $rawMaterial['item_id'],
                'item_number' => $item->item_number,
                'item_name' => $item->name,
                'item_description' => $item->description,
                'consumed_quantity' => $rawMaterial['consumed_quantity'],
                'unit_cost' => $rawMaterial['unit_cost'] ?? 0,
                'warehouse_id' => $rawMaterial['warehouse_id'] ?? $process->raw_materials_warehouse_id,
                'created_by' => $userId,
            ];

            // Get warehouse name
            if ($rawMaterialData['warehouse_id']) {
                $warehouse = Warehouse::find($rawMaterialData['warehouse_id']);
                if ($warehouse) {
                    $rawMaterialData['warehouse_name'] = $warehouse->name;
                }
            }

            // Get unit information
            if (isset($rawMaterial['unit_id'])) {
                $unit = Unit::find($rawMaterial['unit_id']);
                if ($unit) {
                    $rawMaterialData['unit_id'] = $unit->id;
                    $rawMaterialData['unit_name'] = $unit->name;
                }
            }

            ManufacturingProcessRawMaterial::create($rawMaterialData);
        }
    }

    /**
     * Get manufacturing formulas for dropdown.
     */
    public function getManufacturingFormulas(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $formulas = BomItem::with(['item'])
                ->where('company_id', $companyId)
                ->whereNotNull('formula_number')
                ->orderBy('formula_number')
                ->get();

            $formulaOptions = $formulas->map(function ($formula) {
                return [
                    'id' => $formula->id,
                    'formula_number' => $formula->formula_number,
                    'formula_name' => $formula->formula_name,
                    'item_id' => $formula->item_id,
                    'item_number' => $formula->item ? $formula->item->item_number : null,
                    'item_name' => $formula->item ? $formula->item->name : null,
                    'produced_quantity' => $formula->produced_quantity,
                    'display_text' => $formula->formula_number . ' - ' . ($formula->formula_name ?? 'Manufacturing Formula')
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formulaOptions,
                'message' => 'Manufacturing formulas retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving manufacturing formulas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get item details by manufacturing formula.
     */
    public function getItemByFormula(Request $request): JsonResponse
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
     * Get items for dropdown (bidirectional linking).
     */
    public function getItems(Request $request): JsonResponse
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
                    // Get items that can be manufactured using this formula
                    $query->where('id', $formula->item_id);
                }
            }

            $items = $query->orderBy('item_number')->get();

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
                'message' => 'Items retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving items: ' . $e->getMessage()
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
     * Get raw materials for a specific formula.
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
                ->orWhere('id', $formulaId) // Or the formula itself if it has component data
                ->get();

            $rawMaterials = [];

            foreach ($components as $component) {
                if (!$component->item) continue;

                $item = $component->item;
                $availableQuantity = 0;

                // Get available quantity from warehouse if specified
                if ($warehouseId) {
                    $stock = InventoryStock::where('inventory_item_id', $item->id)
                        ->where('warehouse_id', $warehouseId)
                        ->first();

                    $availableQuantity = $stock ? $stock->available_quantity : 0;
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
                    'is_available' => $availableQuantity >= ($component->consumed_quantity ?? 0),
                    'shortage_quantity' => max(0, ($component->consumed_quantity ?? 0) - $availableQuantity),
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
     * Calculate manufacturing process - deduct raw materials and add finished product.
     */
    public function calculate(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $companyId = $user->company_id;

            $process = ManufacturingProcess::with(['rawMaterials'])
                ->forCompany($companyId)
                ->findOrFail($id);

            // Check if process can be calculated
            if ($process->status !== 'draft') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only draft processes can be calculated'
                ], 422);
            }

            // Check raw material availability
            $missingMaterials = $process->getMissingRawMaterials();
            if (!empty($missingMaterials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient raw materials',
                    'missing_materials' => $missingMaterials
                ], 422);
            }

            // Deduct raw materials from warehouse
            $totalRawMaterialCost = 0;
            foreach ($process->rawMaterials as $rawMaterial) {
                // Update inventory stock
                $this->deductRawMaterial($rawMaterial);
                $totalRawMaterialCost += $rawMaterial->total_cost;
            }

            // Add finished product to warehouse
            $this->addFinishedProduct($process);

            // Update process status and costs
            $process->update([
                'status' => 'completed',
                'actual_quantity' => $process->produced_quantity,
                'total_raw_material_cost' => $totalRawMaterialCost,
                'total_manufacturing_cost' => $process->calculateTotalCost(),
                'cost_per_unit' => $process->calculateCostPerUnit(),
                'completion_percentage' => 100,
                'end_date' => now(),
                'updated_by' => $user->id,
            ]);

            // Create inventory movements
            $this->createInventoryMovements($process, $user->id);

            DB::commit();

            // Reload process with relationships
            $process->load([
                'manufacturingFormula',
                'item',
                'rawMaterialsWarehouse',
                'finishedProductWarehouse',
                'rawMaterials.item',
                'creator',
                'updater'
            ]);

            return response()->json([
                'success' => true,
                'data' => new ManufacturingProcessResource($process),
                'message' => 'Manufacturing process calculated successfully',
                'calculation_summary' => [
                    'raw_materials_deducted' => $process->rawMaterials->count(),
                    'total_raw_material_cost' => $totalRawMaterialCost,
                    'finished_products_added' => $process->actual_quantity,
                    'total_manufacturing_cost' => $process->total_manufacturing_cost,
                    'cost_per_unit' => $process->cost_per_unit,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error calculating manufacturing process: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error calculating manufacturing process: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check raw material availability before calculation.
     */
    public function checkAvailability(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $companyId = $user->company_id;

            $process = ManufacturingProcess::with(['rawMaterials.item'])
                ->forCompany($companyId)
                ->findOrFail($id);

            $availabilityCheck = [];
            $allAvailable = true;

            foreach ($process->rawMaterials as $rawMaterial) {
                $currentStock = $rawMaterial->getCurrentStock();
                $isAvailable = $currentStock >= $rawMaterial->consumed_quantity;

                if (!$isAvailable) {
                    $allAvailable = false;
                }

                $availabilityCheck[] = [
                    'item_id' => $rawMaterial->item_id,
                    'item_number' => $rawMaterial->item_number,
                    'item_name' => $rawMaterial->item_name,
                    'required_quantity' => $rawMaterial->consumed_quantity,
                    'available_quantity' => $currentStock,
                    'is_available' => $isAvailable,
                    'shortage_quantity' => max(0, $rawMaterial->consumed_quantity - $currentStock),
                    'unit_name' => $rawMaterial->unit_name,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'all_materials_available' => $allAvailable,
                    'can_proceed' => $allAvailable,
                    'materials_check' => $availabilityCheck,
                    'total_materials' => count($availabilityCheck),
                    'available_materials' => count(array_filter($availabilityCheck, fn($item) => $item['is_available'])),
                    'missing_materials' => count(array_filter($availabilityCheck, fn($item) => !$item['is_available'])),
                ],
                'message' => $allAvailable ? 'All materials are available' : 'Some materials are insufficient'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deduct raw material from inventory.
     */
    private function deductRawMaterial(ManufacturingProcessRawMaterial $rawMaterial): void
    {
        if (!$rawMaterial->warehouse_id || !$rawMaterial->item_id) {
            return;
        }

        // Find inventory stock record
        $stock = InventoryStock::where('inventory_item_id', $rawMaterial->item_id)
            ->where('warehouse_id', $rawMaterial->warehouse_id)
            ->first();

        if ($stock && $stock->available_quantity >= $rawMaterial->consumed_quantity) {
            // Deduct from available quantity
            $stock->quantity -= $rawMaterial->consumed_quantity;
            $stock->available_quantity -= $rawMaterial->consumed_quantity;
            $stock->save();

            // Update raw material status
            $rawMaterial->update([
                'actual_consumed_quantity' => $rawMaterial->consumed_quantity,
                'status' => 'consumed',
            ]);
        }
    }

    /**
     * Add finished product to inventory.
     */
    private function addFinishedProduct(ManufacturingProcess $process): void
    {
        if (!$process->finished_product_warehouse_id || !$process->item_id) {
            return;
        }

        // Find or create inventory stock record for finished product
        $stock = InventoryStock::firstOrCreate([
            'inventory_item_id' => $process->item_id,
            'warehouse_id' => $process->finished_product_warehouse_id,
            'company_id' => $process->company_id,
        ], [
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
            'created_by' => $process->created_by,
        ]);

        // Add produced quantity
        $stock->quantity += $process->produced_quantity;
        $stock->available_quantity += $process->produced_quantity;
        $stock->save();
    }

    /**
     * Create inventory movements for the manufacturing process.
     */
    private function createInventoryMovements(ManufacturingProcess $process, int $userId): void
    {
        // Create outbound movement for raw materials
        if ($process->rawMaterials->count() > 0) {
            $outboundMovement = InventoryMovement::create([
                'company_id' => $process->company_id,
                'user_id' => $userId,
                'movement_number' => 'MFG-OUT-' . $process->id,
                'movement_type' => 'manufacturing',
                'movement_date' => now()->toDateString(),
                'movement_datetime' => now(),
                'warehouse_id' => $process->raw_materials_warehouse_id,
                'warehouse_name' => $process->raw_materials_warehouse_name,
                'movement_description' => 'Raw materials consumed for manufacturing process #' . $process->id,
                'status' => 'confirmed',
                'is_confirmed' => true,
                'confirmed_at' => now(),
                'confirmed_by' => $userId,
                'total_quantity' => $process->rawMaterials->sum('consumed_quantity'),
                'total_value' => $process->total_raw_material_cost,
                'total_items' => $process->rawMaterials->count(),
                'created_by' => $userId,
            ]);

            // Create movement data for each raw material
            foreach ($process->rawMaterials as $rawMaterial) {
                InventoryMovementData::create([
                    'company_id' => $process->company_id,
                    'inventory_movement_id' => $outboundMovement->id,
                    'item_id' => $rawMaterial->item_id,
                    'item_number' => $rawMaterial->item_number,
                    'item_name' => $rawMaterial->item_name,
                    'warehouse_id' => $rawMaterial->warehouse_id,
                    'warehouse_name' => $rawMaterial->warehouse_name,
                    'quantity' => -$rawMaterial->consumed_quantity, // Negative for outbound
                    'unit_cost' => $rawMaterial->unit_cost,
                    'total_cost' => $rawMaterial->total_cost,
                    'created_by' => $userId,
                ]);
            }
        }

        // Create inbound movement for finished product
        $inboundMovement = InventoryMovement::create([
            'company_id' => $process->company_id,
            'user_id' => $userId,
            'movement_number' => 'MFG-IN-' . $process->id,
            'movement_type' => 'manufacturing',
            'movement_date' => now()->toDateString(),
            'movement_datetime' => now(),
            'warehouse_id' => $process->finished_product_warehouse_id,
            'warehouse_name' => $process->finished_product_warehouse_name,
            'movement_description' => 'Finished product from manufacturing process #' . $process->id,
            'status' => 'confirmed',
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
            'total_quantity' => $process->produced_quantity,
            'total_value' => $process->total_manufacturing_cost,
            'total_items' => 1,
            'created_by' => $userId,
        ]);

        // Create movement data for finished product
        InventoryMovementData::create([
            'company_id' => $process->company_id,
            'inventory_movement_id' => $inboundMovement->id,
            'item_id' => $process->item_id,
            'item_number' => $process->item_number,
            'item_name' => $process->item_name,
            'warehouse_id' => $process->finished_product_warehouse_id,
            'warehouse_name' => $process->finished_product_warehouse_name,
            'quantity' => $process->produced_quantity, // Positive for inbound
            'unit_cost' => $process->cost_per_unit,
            'total_cost' => $process->total_manufacturing_cost,
            'created_by' => $userId,
        ]);
    }
}
