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
        Schema::create('manufacturing_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Manufacturing Formula Information
            $table->foreignId('manufacturing_formula_id')->nullable()->constrained('bom_items')->nullOnDelete();
            // manufacturing_formula_number, manufacturing_formula_name removed - available via manufacturingFormula relationship

            // Item Information (Final Product)
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            // item_number, item_name removed - available via item relationship

            // Manufacturing Details
            $table->string('manufacturing_duration')->nullable(); // e.g., "2 days", "5 hours"
            $table->enum('manufacturing_duration_unit', ['minutes', 'hours', 'days', 'weeks', 'months'])->default('days');
            $table->decimal('produced_quantity', 15, 4)->default(0);
            $table->decimal('expected_quantity', 15, 4)->default(0);
            $table->decimal('actual_quantity', 15, 4)->nullable();

            // Warehouse Information
            $table->foreignId('raw_materials_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('finished_product_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            // raw_materials_warehouse_name, finished_product_warehouse_name removed - available via warehouse relationships

            // Process Status
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->date('process_date')->nullable();
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->decimal('completion_percentage', 5, 2)->default(0);

            // Cost Information
            $table->decimal('total_raw_material_cost', 15, 2)->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('overhead_cost', 15, 2)->default(0);
            $table->decimal('total_manufacturing_cost', 15, 2)->default(0);
            $table->decimal('cost_per_unit', 15, 2)->default(0);

            // Additional Information
            $table->text('notes')->nullable();
            $table->boolean('quality_check_passed')->default(false);
            $table->string('batch_number')->nullable();
            $table->string('production_order_number')->nullable();

            // System Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for Performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'manufacturing_formula_id']);
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'process_date']);
            $table->index(['manufacturing_formula_number']);
            $table->index(['batch_number']);
            $table->index(['production_order_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturing_processes');
    }
};
