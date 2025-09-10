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
        Schema::create('item_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            
            // Conversion Information
            $table->decimal('conversion_rate', 12, 6); // معدل التحويل
            $table->boolean('is_default')->default(false); // الوحدة الافتراضية

            // Unit Type and Configuration
            $table->enum('unit_type', ['balance', 'second', 'third'])->default('balance'); // نوع الوحدة
            $table->decimal('quantity_factor', 10, 4)->default(1); // معامل الكمية

            // Balance Unit (وحدة الرصيد)
            $table->enum('balance_unit', ['piece', 'liter', 'kilo', 'ton', 'carton'])->default('piece'); // وحدة الرصيد
            $table->string('custom_balance_unit')->nullable(); // وحدة رصيد مخصصة

            // Dimensions (الأبعاد)
            $table->decimal('length', 10, 2)->nullable(); // الطول
            $table->decimal('width', 10, 2)->nullable(); // العرض
            $table->decimal('height', 10, 2)->nullable(); // الارتفاع

            // Second Unit (الوحدة الثانية)
            $table->enum('second_unit', ['piece', 'liter', 'kilo', 'ton', 'carton'])->nullable(); // الوحدة الثانية
            $table->string('custom_second_unit')->nullable(); // وحدة ثانية مخصصة
            $table->string('second_unit_contains')->default('all'); // تحتوي على - الوحدة الثانية
            $table->string('custom_second_unit_contains')->nullable(); // تحتوي على مخصص - الوحدة الثانية
            $table->text('second_unit_content')->nullable(); // محتوى الوحدة الثانية
            $table->string('second_unit_item_number')->nullable(); // رقم صنف الوحدة الثانية

            // Third Unit (الوحدة الثالثة)
            $table->enum('third_unit', ['piece', 'liter', 'kilo', 'ton', 'carton'])->nullable(); // الوحدة الثالثة
            $table->string('custom_third_unit')->nullable(); // وحدة ثالثة مخصصة
            $table->string('third_unit_contains')->default('all'); // تحتوي على - الوحدة الثالثة
            $table->string('custom_third_unit_contains')->nullable(); // تحتوي على مخصص - الوحدة الثالثة
            $table->text('third_unit_content')->nullable(); // محتوى الوحدة الثالثة
            $table->string('third_unit_item_number')->nullable(); // رقم صنف الوحدة الثالثة

            // Default Units (الوحدات الافتراضية)
            $table->foreignId('default_handling_unit_id')->nullable()->constrained('units')->nullOnDelete(); // وحدة التعامل الافتراضية
            $table->foreignId('default_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete(); // المخزن الافتراضي

            // Contains Information (معلومات المحتويات) - Legacy fields for backward compatibility
            $table->string('contains')->default('all'); // تحتوي على
            $table->string('custom_contains')->nullable(); // تحتوي على مخصص
            $table->text('unit_content')->nullable(); // محتوى الوحدة
            $table->string('unit_item_number')->nullable(); // رقم صنف الوحدة

            // Pricing per Unit (التسعير حسب الوحدة)
            $table->decimal('unit_purchase_price', 12, 2)->nullable(); // سعر الشراء للوحدة
            $table->decimal('unit_sale_price', 12, 2)->nullable(); // سعر البيع للوحدة

            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'unit_id']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['item_id', 'is_default']);
            $table->unique(['item_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_units');
    }
};
