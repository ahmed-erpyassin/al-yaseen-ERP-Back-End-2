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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            
            // Order Information
            $table->string('order_number')->unique(); // رقم أمر الشراء
            $table->date('order_date'); // تاريخ الأمر
            $table->date('delivery_date')->nullable(); // تاريخ التسليم المتوقع
            $table->date('received_date')->nullable(); // تاريخ الاستلام الفعلي
            
            // Financial Information
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete(); // العملة
            $table->decimal('currency_rate', 10, 4)->default(1); // سعر الصرف
            $table->decimal('subtotal', 15, 2)->default(0); // المجموع الفرعي
            $table->decimal('discount_percentage', 5, 2)->default(0); // نسبة الخصم
            $table->decimal('discount_amount', 15, 2)->default(0); // مبلغ الخصم
            $table->decimal('tax_percentage', 5, 2)->default(0); // نسبة الضريبة
            $table->decimal('tax_amount', 15, 2)->default(0); // مبلغ الضريبة
            $table->decimal('total_amount', 15, 2)->default(0); // المبلغ الإجمالي
            
            // Status and Notes
            $table->enum('status', ['draft', 'sent', 'confirmed', 'partially_received', 'received', 'cancelled'])->default('draft'); // الحالة
            $table->text('notes')->nullable(); // ملاحظات
            $table->text('terms_conditions')->nullable(); // الشروط والأحكام

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'order_date']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
