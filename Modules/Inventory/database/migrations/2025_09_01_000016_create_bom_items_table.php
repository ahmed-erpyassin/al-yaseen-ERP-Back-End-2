<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // ✅ Main Item Information (Parent/Finished Product)
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // Parent item (finished product)
            // item_number, item_name removed - available via item relationship

            // ✅ Component Item Information (Raw Material/Sub-assembly)
            $table->foreignId('component_id')->constrained('items')->cascadeOnDelete(); // Component item (raw material/sub-assembly)
            // component_item_number, component_item_name, component_item_description removed - available via component relationship

            // ✅ Unit Information (from Units table)
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete(); // Unit of measurement
            // unit_name, unit_code removed - available via unit relationship

            // ✅ BOM Formula Information
            $table->string('formula_number')->nullable(); // Auto-generated formula number
            $table->string('formula_name')->nullable(); // Name of the manufacturing formula
            $table->text('formula_description')->nullable(); // Description of the formula

            // ✅ Item Details (from Items table)
            $table->decimal('balance', 15, 4)->default(0); // Current balance
            $table->decimal('minimum_limit', 15, 4)->default(0); // Minimum stock limit
            $table->decimal('maximum_limit', 15, 4)->default(0); // Maximum stock limit
            $table->decimal('minimum_reorder_level', 15, 4)->default(0); // Reorder level

            // ✅ Date and Time (automatic on insert)
            $table->date('formula_date')->nullable(); // Date of formula creation
            $table->time('formula_time')->nullable(); // Time of formula creation
            $table->timestamp('formula_datetime')->nullable(); // Combined datetime

            // ✅ Component Quantities and Requirements
            $table->decimal('quantity', 15, 4); // Required quantity of component per unit of parent item (original field enhanced)
            $table->decimal('required_quantity', 15, 4)->default(0); // Required quantity per batch
            $table->decimal('available_quantity', 15, 4)->default(0); // Available in stock
            $table->decimal('consumed_quantity', 15, 4)->default(0); // Actually consumed
            $table->decimal('produced_quantity', 15, 4)->default(0); // Total produced quantity
            $table->decimal('waste_quantity', 15, 4)->default(0); // Waste/loss quantity
            $table->decimal('yield_percentage', 5, 2)->default(100); // Component yield %

            // ✅ Pricing Information (from Sales/Purchases tables)
            $table->decimal('selling_price', 15, 4)->default(0); // Current selling price
            $table->decimal('purchase_price', 15, 4)->default(0); // Current purchase price

            // ✅ Historical Purchase Prices (from Purchases invoices)
            $table->decimal('first_purchase_price', 15, 4)->default(0);
            $table->decimal('second_purchase_price', 15, 4)->default(0);
            $table->decimal('third_purchase_price', 15, 4)->default(0);

            // ✅ Historical Selling Prices (from Sales invoices)
            $table->decimal('first_selling_price', 15, 4)->default(0);
            $table->decimal('second_selling_price', 15, 4)->default(0);
            $table->decimal('third_selling_price', 15, 4)->default(0);

            // ✅ Component Costs
            $table->decimal('unit_cost', 15, 4)->default(0); // Cost per unit
            $table->decimal('total_cost', 15, 4)->default(0); // Total cost for required quantity
            $table->decimal('actual_cost', 15, 4)->default(0); // Actual cost consumed
            $table->decimal('labor_cost', 15, 4)->default(0); // Labor cost
            $table->decimal('operating_cost', 15, 4)->default(0); // Operating cost
            $table->decimal('waste_cost', 15, 4)->default(0); // Waste cost
            $table->decimal('final_cost', 15, 4)->default(0); // Final total cost
            $table->decimal('material_cost', 15, 4)->default(0); // Raw materials cost
            $table->decimal('overhead_cost', 15, 4)->default(0); // Overhead cost
            $table->decimal('total_production_cost', 15, 4)->default(0); // Total production cost
            $table->decimal('cost_per_unit', 15, 4)->default(0); // Cost per unit produced

            // ✅ Component Details (from Items table)
            $table->decimal('component_balance', 15, 4)->default(0); // Current stock balance
            $table->decimal('component_minimum_limit', 15, 4)->default(0); // Minimum stock limit
            $table->decimal('component_maximum_limit', 15, 4)->default(0); // Maximum stock limit
            $table->decimal('reorder_level', 15, 4)->default(0); // Reorder level

            // ✅ Component Type and Properties
            $table->enum('component_type', ['raw_material', 'semi_finished', 'packaging', 'consumable'])->default('raw_material');
            $table->boolean('is_critical')->default(false); // Critical component flag
            $table->boolean('is_optional')->default(false); // Optional component flag
            $table->integer('sequence_order')->default(1); // Order in production process

            // ✅ Formula Status and Control
            $table->enum('status', ['draft', 'active', 'inactive', 'archived'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable(); // When formula becomes effective
            $table->date('effective_to')->nullable(); // When formula expires

            // ✅ Production Information
            $table->decimal('batch_size', 15, 4)->default(1); // Standard batch size
            $table->integer('production_time_minutes')->default(0); // Time to produce
            $table->integer('preparation_time_minutes')->default(0); // Prep time required
            $table->text('production_notes')->nullable(); // Production instructions
            $table->text('preparation_notes')->nullable(); // How to prepare component
            $table->text('usage_instructions')->nullable(); // How to use in production

            // ✅ Quality Control
            $table->decimal('tolerance_percentage', 5, 2)->default(0); // Acceptable variance %
            $table->text('quality_requirements')->nullable(); // Quality specifications
            $table->boolean('requires_inspection')->default(false); // Needs quality check

            // ✅ Supplier Information
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_item_code')->nullable(); // Supplier's item code
            $table->decimal('supplier_unit_price', 15, 4)->default(0); // Supplier price
            $table->integer('lead_time_days')->default(0); // Supplier lead time

            // ✅ Audit Fields (Enhanced)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ✅ Indexes for Performance
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'component_id']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'status']);
            $table->index(['formula_number']);
            $table->index(['formula_date']);
            $table->index(['is_active']);
            $table->index(['component_type']);
            $table->index(['is_critical']);
            $table->index(['sequence_order']);
            $table->unique(['item_id', 'component_id']); // Prevent duplicate components in same BOM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
