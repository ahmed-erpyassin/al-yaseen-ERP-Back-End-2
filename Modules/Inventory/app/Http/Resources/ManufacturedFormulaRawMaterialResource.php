<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturedFormulaRawMaterialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'manufactured_formula_id' => $this->manufactured_formula_id,
            
            // ✅ Item Information (via relationship)
            'item_id' => $this->item_id,
            'item_number' => $this->item?->item_number,
            'item_name' => $this->item?->name,
            'item_description' => $this->item?->description,
            'item_color' => $this->item?->color,
            'item_balance' => $this->item?->balance,
            
            // ✅ Unit Information (via relationship)
            'unit_id' => $this->unit_id,
            'unit_name' => $this->unit?->name,
            'unit_code' => $this->unit?->code,
            
            // ✅ Warehouse Information (via relationship)
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse?->name,
            'warehouse_number' => $this->warehouse?->warehouse_number,
            
            // ✅ Quantity Information
            'consumed_quantity' => $this->consumed_quantity,
            'available_quantity' => $this->available_quantity,
            'required_quantity' => $this->required_quantity,
            'actual_consumed_quantity' => $this->actual_consumed_quantity,
            'waste_quantity' => $this->waste_quantity,
            
            // ✅ Cost Information
            'unit_cost' => $this->unit_cost,
            'total_cost' => $this->total_cost,
            'actual_cost' => $this->actual_cost,
            
            // ✅ Material Properties
            'material_type' => $this->material_type,
            'material_type_label' => $this->material_type ? (ManufacturedFormulaRawMaterial::MATERIAL_TYPES[$this->material_type] ?? $this->material_type) : null,
            'is_critical' => $this->is_critical,
            'sequence_order' => $this->sequence_order,
            
            // ✅ Quality Control
            'quality_requirements' => $this->quality_requirements,
            'requires_inspection' => $this->requires_inspection,
            'inspection_passed' => $this->inspection_passed,
            
            // ✅ Additional Information
            'notes' => $this->notes,
            'preparation_instructions' => $this->preparation_instructions,
            'usage_instructions' => $this->usage_instructions,
            
            // ✅ Availability Check
            'availability' => $this->when($this->item_id && $this->warehouse_id, function () {
                return $this->checkAvailability();
            }),
            
            // ✅ Current Stock
            'current_stock' => $this->when($this->item_id && $this->warehouse_id, function () {
                return $this->getCurrentStock();
            }),
            
            // ✅ Audit Information
            'created_by' => $this->created_by,
            'creator_name' => $this->creator?->name,
            'updated_by' => $this->updated_by,
            'updater_name' => $this->updater?->name,
            'deleted_by' => $this->deleted_by,
            'deleter_name' => $this->deleter?->name,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // ✅ Company Information
            'company_id' => $this->company_id,
            'branch_id' => $this->branch_id,
            'user_id' => $this->user_id,
        ];
    }
}
