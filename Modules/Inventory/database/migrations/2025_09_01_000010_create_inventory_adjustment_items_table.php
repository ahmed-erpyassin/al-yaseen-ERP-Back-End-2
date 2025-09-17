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
        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained('inventory_adjustments')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();

            // Quantity Information
            $table->decimal('system_quantity', 12, 2); // الكمية في النظام
            $table->decimal('physical_quantity', 12, 2); // الكمية الفعلية
            $table->decimal('difference_quantity', 12, 2); // فرق الكمية
            $table->decimal('unit_cost', 12, 2)->nullable(); // تكلفة الوحدة
            $table->decimal('total_cost', 12, 2)->nullable(); // التكلفة الإجمالية

            // Additional Information
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('batch_number')->nullable(); // رقم الدفعة
            $table->date('expiry_date')->nullable(); // تاريخ الانتهاء

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['inventory_adjustment_id', 'inventory_item_id'], 'inv_adj_items_adj_id_item_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
    }
};
