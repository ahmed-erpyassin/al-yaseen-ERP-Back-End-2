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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();

            // Basic Item Information
            $table->string('item_number')->unique(); // رقم الصنف
            $table->string('item_name_ar'); // اسم الصنف
            $table->string('item_name_en')->nullable(); // Item Name (English)
            $table->string('barcode')->nullable()->unique(); // باركود
            $table->string('model')->nullable(); // موديل
            $table->string('unit'); // الوحدة

            // Category and Supplier Relations
            $table->foreignId('category_id')->nullable()->constrained('item_categories')->nullOnDelete(); // التصنيف
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete(); // المورد

            // Quantities and Limits
            $table->decimal('quantity', 12, 2)->default(0); // الكمية
            $table->decimal('minimum_limit', 12, 2)->default(0); // الحد الأدنى
            $table->decimal('reorder_limit', 12, 2)->default(0); // حد إعادة الطلب

            // Pricing Information
            $table->decimal('unit_price', 12, 2)->default(0); // سعر الوحدة
            $table->decimal('first_purchase_price', 12, 2)->default(0); // سعر الشراء الأول
            $table->decimal('second_purchase_price', 12, 2)->default(0); // سعر الشراء الثاني
            $table->decimal('third_purchase_price', 12, 2)->default(0); // سعر الشراء الثالث
            $table->decimal('first_sale_price', 12, 2)->default(0); // سعر البيع الأول
            $table->decimal('second_sale_price', 12, 2)->default(0); // سعر البيع الثاني
            $table->decimal('third_sale_price', 12, 2)->default(0); // سعر البيع الثالث

            // Additional Information
            $table->text('notes')->nullable(); // ملاحظات
            $table->boolean('active')->default(true); // نشط

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'item_number']);
            $table->index(['company_id', 'active']);
            $table->index(['company_id', 'barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
