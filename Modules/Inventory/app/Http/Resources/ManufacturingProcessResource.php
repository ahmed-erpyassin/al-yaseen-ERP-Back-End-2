<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturingProcessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Manufacturing Formula Information
            'manufacturing_formula_id' => $this->manufacturing_formula_id,
            'manufacturing_formula_number' => $this->manufacturing_formula_number,
            'manufacturing_formula_name' => $this->manufacturing_formula_name,
            'manufacturing_formula' => $this->whenLoaded('manufacturingFormula', function () {
                return [
                    'id' => $this->manufacturingFormula->id,
                    'formula_number' => $this->manufacturingFormula->formula_number,
                    'formula_name' => $this->manufacturingFormula->formula_name,
                    'formula_description' => $this->manufacturingFormula->formula_description,
                ];
            }),
            
            // Item Information (Final Product)
            'item_id' => $this->item_id,
            'item_number' => $this->item_number,
            'item_name' => $this->item_name,
            'item' => $this->whenLoaded('item', function () {
                return [
                    'id' => $this->item->id,
                    'item_number' => $this->item->item_number,
                    'name' => $this->item->name,
                    'description' => $this->item->description,
                    'unit_id' => $this->item->unit_id,
                    'unit_name' => $this->item->unit_name,
                ];
            }),
            
            // Manufacturing Details
            'manufacturing_duration' => $this->manufacturing_duration,
            'manufacturing_duration_unit' => $this->manufacturing_duration_unit,
            'manufacturing_duration_display' => $this->manufacturing_duration . ' ' . ($this->manufacturing_duration_unit ?? 'days'),
            'produced_quantity' => $this->produced_quantity,
            'expected_quantity' => $this->expected_quantity,
            'actual_quantity' => $this->actual_quantity,
            
            // Warehouse Information
            'raw_materials_warehouse_id' => $this->raw_materials_warehouse_id,
            'finished_product_warehouse_id' => $this->finished_product_warehouse_id,
            'raw_materials_warehouse_name' => $this->raw_materials_warehouse_name,
            'finished_product_warehouse_name' => $this->finished_product_warehouse_name,
            'raw_materials_warehouse' => $this->whenLoaded('rawMaterialsWarehouse', function () {
                return [
                    'id' => $this->rawMaterialsWarehouse->id,
                    'warehouse_number' => $this->rawMaterialsWarehouse->warehouse_number,
                    'name' => $this->rawMaterialsWarehouse->name,
                    'address' => $this->rawMaterialsWarehouse->address,
                ];
            }),
            'finished_product_warehouse' => $this->whenLoaded('finishedProductWarehouse', function () {
                return [
                    'id' => $this->finishedProductWarehouse->id,
                    'warehouse_number' => $this->finishedProductWarehouse->warehouse_number,
                    'name' => $this->finishedProductWarehouse->name,
                    'address' => $this->finishedProductWarehouse->address,
                ];
            }),
            
            // Process Status
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'process_date' => $this->process_date?->format('Y-m-d'),
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'completion_percentage' => $this->completion_percentage,
            'completion_percentage_display' => number_format($this->completion_percentage, 1) . '%',
            
            // Cost Information
            'total_raw_material_cost' => $this->total_raw_material_cost,
            'labor_cost' => $this->labor_cost,
            'overhead_cost' => $this->overhead_cost,
            'total_manufacturing_cost' => $this->total_manufacturing_cost,
            'cost_per_unit' => $this->cost_per_unit,
            'cost_breakdown' => [
                'raw_materials' => $this->total_raw_material_cost,
                'labor' => $this->labor_cost,
                'overhead' => $this->overhead_cost,
                'total' => $this->total_manufacturing_cost,
                'per_unit' => $this->cost_per_unit,
            ],
            
            // Additional Information
            'notes' => $this->notes,
            'quality_check_passed' => $this->quality_check_passed,
            'batch_number' => $this->batch_number,
            'production_order_number' => $this->production_order_number,
            
            // Raw Materials
            'raw_materials' => $this->whenLoaded('rawMaterials', function () {
                return $this->rawMaterials->map(function ($rawMaterial) {
                    return [
                        'id' => $rawMaterial->id,
                        'item_id' => $rawMaterial->item_id,
                        'item_number' => $rawMaterial->item_number,
                        'item_name' => $rawMaterial->item_name,
                        'item_description' => $rawMaterial->item_description,
                        'unit_id' => $rawMaterial->unit_id,
                        'unit_name' => $rawMaterial->unit_name,
                        'warehouse_id' => $rawMaterial->warehouse_id,
                        'warehouse_name' => $rawMaterial->warehouse_name,
                        'consumed_quantity' => $rawMaterial->consumed_quantity,
                        'available_quantity' => $rawMaterial->available_quantity,
                        'actual_consumed_quantity' => $rawMaterial->actual_consumed_quantity,
                        'shortage_quantity' => $rawMaterial->shortage_quantity,
                        'unit_cost' => $rawMaterial->unit_cost,
                        'total_cost' => $rawMaterial->total_cost,
                        'actual_total_cost' => $rawMaterial->actual_total_cost,
                        'status' => $rawMaterial->status,
                        'is_available' => $rawMaterial->is_available,
                        'is_critical' => $rawMaterial->is_critical,
                        'batch_number' => $rawMaterial->batch_number,
                        'expiry_date' => $rawMaterial->expiry_date?->format('Y-m-d'),
                        'notes' => $rawMaterial->notes,
                    ];
                });
            }),
            
            // Raw Materials Summary
            'raw_materials_summary' => $this->whenLoaded('rawMaterials', function () {
                return [
                    'total_items' => $this->rawMaterials->count(),
                    'available_items' => $this->rawMaterials->where('is_available', true)->count(),
                    'insufficient_items' => $this->rawMaterials->where('is_available', false)->count(),
                    'critical_items' => $this->rawMaterials->where('is_critical', true)->count(),
                    'total_cost' => $this->rawMaterials->sum('total_cost'),
                    'total_consumed_quantity' => $this->rawMaterials->sum('consumed_quantity'),
                ];
            }),
            
            // Process Capabilities
            'can_start' => $this->canStart(),
            'can_calculate' => $this->status === 'draft' && $this->checkRawMaterialAvailability(),
            'missing_materials' => $this->getMissingRawMaterials(),
            
            // User Information
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                    'email' => $this->updater->email,
                ];
            }),
            
            // System Fields
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // Display Information
            'display_name' => $this->getDisplayName(),
            'progress_info' => $this->getProgressInfo(),
            'duration_info' => $this->getDurationInfo(),
        ];
    }

    /**
     * Get status display text.
     */
    private function getStatusDisplay(): string
    {
        $statusOptions = [
            'draft' => 'مسودة',
            'in_progress' => 'قيد التنفيذ',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
        ];

        return $statusOptions[$this->status] ?? $this->status;
    }

    /**
     * Get display name for the manufacturing process.
     */
    private function getDisplayName(): string
    {
        $parts = [];
        
        if ($this->manufacturing_formula_number) {
            $parts[] = $this->manufacturing_formula_number;
        }
        
        if ($this->item_name) {
            $parts[] = $this->item_name;
        }
        
        if ($this->batch_number) {
            $parts[] = 'Batch: ' . $this->batch_number;
        }
        
        return implode(' - ', $parts) ?: 'Manufacturing Process #' . $this->id;
    }

    /**
     * Get progress information.
     */
    private function getProgressInfo(): array
    {
        return [
            'percentage' => $this->completion_percentage,
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'is_completed' => $this->status === 'completed',
            'is_in_progress' => $this->status === 'in_progress',
            'is_draft' => $this->status === 'draft',
            'is_cancelled' => $this->status === 'cancelled',
        ];
    }

    /**
     * Get duration information.
     */
    private function getDurationInfo(): array
    {
        $info = [
            'duration' => $this->manufacturing_duration,
            'unit' => $this->manufacturing_duration_unit,
            'display' => $this->manufacturing_duration . ' ' . ($this->manufacturing_duration_unit ?? 'days'),
        ];

        if ($this->start_date && $this->end_date) {
            $info['actual_duration'] = $this->start_date->diffForHumans($this->end_date, true);
            $info['duration_in_hours'] = $this->start_date->diffInHours($this->end_date);
            $info['duration_in_days'] = $this->start_date->diffInDays($this->end_date);
        }

        return $info;
    }
}
