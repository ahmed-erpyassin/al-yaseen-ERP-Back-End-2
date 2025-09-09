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
        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            
            // Quantity Information
            $table->decimal('quantity_sent', 12, 2); // الكمية المرسلة
            $table->decimal('quantity_received', 12, 2)->default(0); // الكمية المستلمة
            $table->decimal('quantity_damaged', 12, 2)->default(0); // الكمية التالفة
            $table->string('unit'); // الوحدة
            
            // Cost Information
            $table->decimal('unit_cost', 12, 2)->nullable(); // تكلفة الوحدة
            $table->decimal('total_cost', 12, 2)->nullable(); // التكلفة الإجمالية
            
            // Additional Information
            $table->text('notes')->nullable(); // ملاحظات
            $table->string('batch_number')->nullable(); // رقم الدفعة
            $table->date('expiry_date')->nullable(); // تاريخ الانتهاء
            $table->enum('condition', ['good', 'damaged', 'expired'])->default('good'); // الحالة

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stock_transfer_id', 'inventory_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_items');
    }
};
