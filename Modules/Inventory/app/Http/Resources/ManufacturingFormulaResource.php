<?php

namespace Modules\Inventory\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturingFormulaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // ✅ Formula Information
            'formula_number' => $this->formula_number,
            'formula_name' => $this->formula_name,
            'formula_description' => $this->formula_description,
            'formula_date' => $this->formula_date,
            'formula_time' => $this->formula_time,
            'formula_datetime' => $this->formula_datetime?->format('Y-m-d H:i:s'),
            
            // ✅ Item Information (Final Product)
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
                    'balance' => $this->item->balance,
                    'minimum_limit' => $this->item->minimum_limit,
                    'maximum_limit' => $this->item->maximum_limit,
                ];
            }),
            
            // ✅ Unit Information
            'unit_id' => $this->unit_id,
            'unit_name' => $this->unit_name,
            'unit_code' => $this->unit_code,
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                    'code' => $this->unit->code,
                    'symbol' => $this->unit->symbol,
                ];
            }),
            
            // ✅ Manufacturing Details
            'manufacturing_duration' => $this->manufacturing_duration,
            'manufacturing_duration_unit' => $this->manufacturing_duration_unit ?? 'days',
            'manufacturing_duration_display' => $this->manufacturing_duration . ' ' . ($this->manufacturing_duration_unit ?? 'days'),
            
            // ✅ Quantities
            'consumed_quantity' => $this->consumed_quantity,
            'produced_quantity' => $this->produced_quantity,
            'batch_size' => $this->batch_size,
            
            // ✅ Item Balance and Limits
            'balance' => $this->balance,
            'minimum_limit' => $this->minimum_limit,
            'maximum_limit' => $this->maximum_limit,
            'minimum_reorder_level' => $this->minimum_reorder_level,
            
            // ✅ Purchase Prices (from invoices)
            'first_purchase_price' => $this->first_purchase_price,
            'second_purchase_price' => $this->second_purchase_price,
            'third_purchase_price' => $this->third_purchase_price,
            'selected_purchase_price_type' => $this->selected_purchase_price_type,
            'selected_purchase_price' => $this->getSelectedPurchasePrice(),
            
            // ✅ Selling Prices (from invoices)
            'first_selling_price' => $this->first_selling_price,
            'second_selling_price' => $this->second_selling_price,
            'third_selling_price' => $this->third_selling_price,
            'selling_price' => $this->selling_price,
            'purchase_price' => $this->purchase_price,
            
            // ✅ Cost Information
            'labor_cost' => $this->labor_cost,
            'operating_cost' => $this->operating_cost,
            'waste_cost' => $this->waste_cost,
            'material_cost' => $this->material_cost,
            'final_cost' => $this->final_cost,
            'total_production_cost' => $this->total_production_cost,
            'cost_per_unit' => $this->cost_per_unit,
            
            // ✅ Cost Breakdown
            'cost_breakdown' => [
                'labor' => $this->labor_cost,
                'operating' => $this->operating_cost,
                'waste' => $this->waste_cost,
                'material' => $this->material_cost,
                'total' => $this->total_production_cost,
                'per_unit' => $this->cost_per_unit,
            ],
            
            // ✅ Time Information
            'production_time_minutes' => $this->production_time_minutes,
            'preparation_time_minutes' => $this->preparation_time_minutes,
            'total_time_minutes' => ($this->production_time_minutes ?? 0) + ($this->preparation_time_minutes ?? 0),
            'production_time_display' => $this->formatTimeMinutes($this->production_time_minutes),
            'preparation_time_display' => $this->formatTimeMinutes($this->preparation_time_minutes),
            'total_time_display' => $this->formatTimeMinutes(($this->production_time_minutes ?? 0) + ($this->preparation_time_minutes ?? 0)),
            
            // ✅ Notes and Instructions
            'production_notes' => $this->production_notes,
            'preparation_notes' => $this->preparation_notes,
            'usage_instructions' => $this->usage_instructions,
            
            // ✅ Quality Control
            'tolerance_percentage' => $this->tolerance_percentage,
            'quality_requirements' => $this->quality_requirements,
            'requires_inspection' => $this->requires_inspection,
            
            // ✅ Status Information
            'status' => $this->status,
            'status_display' => $this->getStatusDisplay(),
            'is_active' => $this->is_active,
            'component_type' => $this->component_type,
            'sequence_order' => $this->sequence_order,
            
            // ✅ Effective Dates
            'effective_from' => $this->effective_from?->format('Y-m-d'),
            'effective_to' => $this->effective_to?->format('Y-m-d'),
            'is_effective' => $this->isEffective(),
            
            // ✅ User Information
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
            'deleter' => $this->whenLoaded('deleter', function () {
                return [
                    'id' => $this->deleter->id,
                    'name' => $this->deleter->name,
                    'email' => $this->deleter->email,
                ];
            }),
            
            // ✅ System Fields
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'deleted_by' => $this->deleted_by,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),
            
            // ✅ Display Information
            'display_name' => $this->getDisplayName(),
            'summary_info' => $this->getSummaryInfo(),
            'efficiency_metrics' => $this->getEfficiencyMetrics(),
            
            // ✅ Additional Computed Fields
            'is_deleted' => $this->deleted_at !== null,
            'can_edit' => $this->canEdit(),
            'can_delete' => $this->canDelete(),
            'can_restore' => $this->deleted_at !== null,
        ];
    }

    /**
     * Get selected purchase price based on type.
     */
    private function getSelectedPurchasePrice(): float
    {
        $priceSelection = $this->selected_purchase_price_type ?? 'first';

        switch ($priceSelection) {
            case 'first':
                return $this->first_purchase_price ?? 0;
            case 'second':
                return $this->second_purchase_price ?? 0;
            case 'third':
                return $this->third_purchase_price ?? 0;
            default:
                return $this->first_purchase_price ?? 0;
        }
    }

    /**
     * Get status display text.
     */
    private function getStatusDisplay(): string
    {
        $statusOptions = [
            'draft' => 'مسودة',
            'active' => 'نشط',
            'inactive' => 'غير نشط',
            'archived' => 'مؤرشف',
        ];

        return $statusOptions[$this->status] ?? $this->status;
    }

    /**
     * Check if formula is currently effective.
     */
    private function isEffective(): bool
    {
        $now = now()->toDateString();
        
        $effectiveFrom = $this->effective_from ? $this->effective_from->toDateString() : null;
        $effectiveTo = $this->effective_to ? $this->effective_to->toDateString() : null;
        
        if ($effectiveFrom && $now < $effectiveFrom) {
            return false;
        }
        
        if ($effectiveTo && $now > $effectiveTo) {
            return false;
        }
        
        return true;
    }

    /**
     * Format time in minutes to human readable format.
     */
    private function formatTimeMinutes(?int $minutes): string
    {
        if (!$minutes) {
            return '0 minutes';
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return $remainingMinutes > 0 ? 
                "{$hours} hours {$remainingMinutes} minutes" : 
                "{$hours} hours";
        }

        return "{$minutes} minutes";
    }

    /**
     * Get display name for the formula.
     */
    private function getDisplayName(): string
    {
        $parts = [];
        
        if ($this->formula_number) {
            $parts[] = $this->formula_number;
        }
        
        if ($this->formula_name) {
            $parts[] = $this->formula_name;
        } elseif ($this->item_name) {
            $parts[] = $this->item_name;
        }
        
        return implode(' - ', $parts) ?: 'Manufacturing Formula #' . $this->id;
    }

    /**
     * Get summary information.
     */
    private function getSummaryInfo(): array
    {
        return [
            'formula_number' => $this->formula_number,
            'item_name' => $this->item_name,
            'produced_quantity' => $this->produced_quantity,
            'total_cost' => $this->total_production_cost,
            'cost_per_unit' => $this->cost_per_unit,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'is_effective' => $this->isEffective(),
        ];
    }

    /**
     * Get efficiency metrics.
     */
    private function getEfficiencyMetrics(): array
    {
        $totalTime = ($this->production_time_minutes ?? 0) + ($this->preparation_time_minutes ?? 0);
        
        return [
            'total_time_minutes' => $totalTime,
            'cost_per_minute' => $totalTime > 0 ? ($this->total_production_cost ?? 0) / $totalTime : 0,
            'units_per_minute' => $totalTime > 0 ? ($this->produced_quantity ?? 0) / $totalTime : 0,
            'efficiency_ratio' => $this->produced_quantity > 0 ? 
                ($this->consumed_quantity ?? 0) / $this->produced_quantity : 0,
        ];
    }

    /**
     * Check if formula can be edited.
     */
    private function canEdit(): bool
    {
        return $this->status !== 'archived' && $this->deleted_at === null;
    }

    /**
     * Check if formula can be deleted.
     */
    private function canDelete(): bool
    {
        return $this->deleted_at === null;
    }
}
