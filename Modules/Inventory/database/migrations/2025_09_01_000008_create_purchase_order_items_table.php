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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            
            // Item Details
            $table->string('item_description')->nullable(); // وصف الصنف
            $table->string('unit'); // الوحدة
            $table->decimal('quantity_ordered', 12, 2); // الكمية المطلوبة
            $table->decimal('quantity_received', 12, 2)->default(0); // الكمية المستلمة
            $table->decimal('quantity_remaining', 12, 2)->default(0); // الكمية المتبقية
            
            // Pricing
            $table->decimal('unit_price', 12, 2); // سعر الوحدة
            $table->decimal('discount_percentage', 5, 2)->default(0); // نسبة الخصم
            $table->decimal('discount_amount', 12, 2)->default(0); // مبلغ الخصم
            $table->decimal('net_unit_price', 12, 2); // سعر الوحدة الصافي
            $table->decimal('total_amount', 12, 2); // المبلغ الإجمالي
            
            // Status
            $table->enum('status', ['pending', 'partially_received', 'received', 'cancelled'])->default('pending'); // الحالة
            $table->text('notes')->nullable(); // ملاحظات

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'inventory_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
