<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Create Manufactured Formulas Table (المعادلات المصنعة).
     *
     * This table stores manufactured formulas with all required fields:
     * - Formula Number (serial with prefix)
     * - Item details (from items table via relationships)
     * - Manufacturing details (duration, quantities)
     * - Warehouse information
     * - Raw materials tracking
     */
    public function up(): void
    {
        Schema::create('manufactured_formulas', function (Blueprint $table) {
            $table->id();

            // ✅ Company and User Information
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // ✅ Formula Information
            $table->string('formula_number')->unique(); // رقم المعادلة (serial with prefix)

            // ✅ Item Information (Final Product) - Use relationships, no redundant fields
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            // item_number, item_name, unit, balance, limits, prices, color, dimensions - all via item relationship

            // ✅ Manufacturing Details
            $table->string('manufacturing_duration')->nullable(); // مدة التصنيع (e.g., "2 days", "1 month")
            $table->enum('manufacturing_duration_unit', ['minutes', 'hours', 'days', 'weeks', 'months', 'years'])->default('days');
            $table->integer('manufacturing_duration_value')->nullable(); // Numeric value for calculations

            // ✅ Quantities
            $table->decimal('consumed_quantity', 15, 4)->default(0); // الكمية المستهلكة
            $table->decimal('produced_quantity', 15, 4)->default(0); // الكمية المنتجة

            // ✅ Warehouse Information
            $table->foreignId('raw_materials_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('finished_product_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();

            // ✅ Manufacturing Process Status
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_active')->default(true);

            // ✅ Cost Information
            $table->decimal('total_raw_material_cost', 15, 4)->default(0);
            $table->decimal('labor_cost', 15, 4)->default(0);
            $table->decimal('overhead_cost', 15, 4)->default(0);
            $table->decimal('total_manufacturing_cost', 15, 4)->default(0);
            $table->decimal('cost_per_unit', 15, 4)->default(0);

            // ✅ Pricing Information (from Suppliers table)
            $table->decimal('sale_price', 15, 4)->default(0); // سعر البيع من جدول الموردين
            $table->decimal('purchase_price', 15, 4)->default(0); // سعر الشراء من جدول الموردين

            // ✅ Date and Time Information (automatic)
            $table->date('formula_date')->nullable();
            $table->time('formula_time')->nullable();
            $table->timestamp('formula_datetime')->useCurrent(); // هذا مسموح

            // ✅ Manufacturing Schedule
            $table->date('start_date')->nullable(); // تاريخ بداية التصنيع
            $table->date('end_date')->nullable(); // تاريخ انتهاء التصنيع
            $table->date('expected_completion_date')->nullable(); // التاريخ المتوقع للإنجاز

            // ✅ Additional Information
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('batch_number')->nullable(); // رقم الدفعة
            $table->string('production_order_number')->nullable(); // رقم أمر الإنتاج

            // ✅ Quality Control
            $table->boolean('requires_quality_check')->default(false);
            $table->text('quality_requirements')->nullable();
            $table->enum('quality_status', ['pending', 'passed', 'failed', 'not_required'])->default('not_required');

            // ✅ System Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // ✅ Indexes for Performance
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_active']);
            $table->index(['formula_number']);
            $table->index(['formula_date']);
            $table->index(['start_date', 'end_date']);
            $table->index(['raw_materials_warehouse_id']);
            $table->index(['finished_product_warehouse_id']);
            $table->unique(['company_id', 'formula_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufactured_formulas');
    }
};
