<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Create Manufactured Formula Raw Materials Table.
     *
     * This table stores raw materials for each manufactured formula:
     * - Links to manufactured formula
     * - Item details (via relationships)
     * - Quantities and availability
     * - Warehouse information
     */
    public function up(): void
    {
        Schema::create('manufactured_formula_raw_materials', function (Blueprint $table) {
            $table->id();

            // ✅ References
            $table->unsignedBigInteger('manufactured_formula_id');
            $table->foreign('manufactured_formula_id', 'mfg_formula_raw_mat_formula_fk')->references('id')->on('manufactured_formulas')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // ✅ Item Information (Raw Material) - Use relationships, no redundant fields
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            // item_number, item_name, item_description - all via item relationship

            // ✅ Unit Information - Use relationships, no redundant fields
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            // unit_name, unit_code - all via unit relationship

            // ✅ Warehouse Information - Use relationships, no redundant fields
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            // warehouse_name - via warehouse relationship

            // ✅ Quantity Information
            $table->decimal('consumed_quantity', 15, 4)->default(0); // الكمية المستهلكة المطلوبة
            $table->decimal('available_quantity', 15, 4)->default(0); // الكمية المتاحة في المخزن
            $table->decimal('required_quantity', 15, 4)->default(0); // الكمية المطلوبة (calculated)
            $table->decimal('shortage_quantity', 15, 4)->default(0); // الكمية الناقصة (calculated)

            // ✅ Cost Information
            $table->decimal('unit_cost', 15, 4)->default(0); // تكلفة الوحدة
            $table->decimal('total_cost', 15, 4)->default(0); // التكلفة الإجمالية

            // ✅ Pricing Information (from Suppliers table)
            $table->decimal('sale_price', 15, 4)->default(0); // سعر البيع من جدول الموردين
            $table->decimal('purchase_price', 15, 4)->default(0); // سعر الشراء من جدول الموردين

            // ✅ Status and Availability
            $table->boolean('is_available')->default(true); // متاح في المخزن
            $table->boolean('is_sufficient')->default(true); // الكمية كافية
            $table->enum('availability_status', ['available', 'insufficient', 'unavailable'])->default('available');

            // ✅ Material Properties
            $table->boolean('is_critical')->default(false); // مادة حرجة
            $table->boolean('is_optional')->default(false); // مادة اختيارية
            $table->integer('sequence_order')->default(1); // ترتيب الاستخدام

            // ✅ Quality Requirements
            $table->text('quality_specifications')->nullable(); // مواصفات الجودة
            $table->decimal('tolerance_percentage', 5, 2)->default(0); // نسبة التسامح

            // ✅ Supplier Information (Optional)
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_item_code')->nullable(); // كود المادة عند المورد
            $table->decimal('supplier_unit_price', 15, 4)->nullable(); // سعر الوحدة عند المورد
            $table->integer('lead_time_days')->nullable(); // مدة التوريد بالأيام

            // ✅ Usage Instructions
            $table->text('usage_instructions')->nullable(); // تعليمات الاستخدام
            $table->text('handling_notes')->nullable(); // ملاحظات التعامل
            $table->text('safety_notes')->nullable(); // ملاحظات السلامة

            // ✅ System Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // ✅ Indexes for Performance (with shorter names)
            $table->index(['manufactured_formula_id', 'item_id'], 'mfg_formula_raw_mat_formula_item_idx');
            $table->index(['company_id', 'item_id'], 'mfg_formula_raw_mat_company_item_idx');
            $table->index(['warehouse_id', 'item_id'], 'mfg_formula_raw_mat_warehouse_item_idx');
            $table->index(['is_available', 'is_sufficient'], 'mfg_formula_raw_mat_availability_idx');
            $table->index(['availability_status'], 'mfg_formula_raw_mat_status_idx');
            $table->index(['is_critical'], 'mfg_formula_raw_mat_critical_idx');
            $table->index(['sequence_order'], 'mfg_formula_raw_mat_sequence_idx');
            $table->unique(['manufactured_formula_id', 'item_id'], 'mfg_formula_raw_mat_unique'); // Prevent duplicate items in same formula
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufactured_formula_raw_materials');
    }
};
