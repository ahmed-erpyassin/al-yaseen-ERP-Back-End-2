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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();

            // Warehouse Information
            $table->string('name'); // اسم المخزن
            $table->string('code')->nullable(); // رقم المخزن
            $table->string('location')->nullable(); // الموقع

            // Responsible Person Information (FK?)
            $table->string('warehouse_keeper_employee_number')->nullable(); // رقم الموظف أمين المخزن
            $table->string('warehouse_keeper_name')->nullable(); // اسم الموظف أمين المخزن

            // Contact Information
            $table->string('mobile')->nullable(); // الجوال
            $table->string('fax_number')->nullable(); // رقم الفاكس
            $table->string('phone_number')->nullable(); // رقم الجوال

            // Department Information (FK)
            $table->foreignId('department_warehouse_id')->nullable()->constrained('department_warehouses')->nullOnDelete(); // القسم

            // Purchase and Sale Account Information
            $table->string('purchase_account')->nullable(); // حساب الشراء
            $table->string('sale_account')->nullable(); // حساب البيع

            // Inventory Valuation Method
            $table->enum('inventory_valuation_method', [
                'natural_division', // طبيعي - كما هو محدد لخطة التقسيم
                'no_value', // بدون - ليس للبضاعة أي قيمة
                'first_purchase_price', // حسب سعر الشراء الأول الموجود في كرت الصنف
                'second_purchase_price', // حسب سعر الشراء الثاني الموجود في كرت الصنف
                'third_purchase_price' // حسب سعر الشراء الثالث الموجود في كرت الصنف
            ])->default('natural_division');

            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'branch_id']);
            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
