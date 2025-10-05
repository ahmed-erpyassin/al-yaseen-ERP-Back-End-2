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
            $table->string('warehouse_number')->unique(); // رقم المخزن (Warehouse Number)
            $table->string('name'); // اسم المخزن (Warehouse Name)
            $table->text('address')->nullable(); // العنوان (Address)
            $table->text('description')->nullable(); // الوصف (Description)
            $table->json('warehouse_data')->nullable(); // بيانات المخزن (Warehouse Data)

            // Warehouse Keeper Information (skip foreign key for now)
            $table->unsignedBigInteger('warehouse_keeper_id')->nullable(); // أمين المخزن (Employee ID)
            $table->string('warehouse_keeper_employee_number')->nullable(); // رقم الموظف أمين المخزن (Warehouse Keeper Employee Number)
            $table->string('warehouse_keeper_employee_name')->nullable(); // اسم الموظف أمين المخزن (Warehouse Keeper Employee Name)

            // Contact Information
            $table->string('phone_number')->nullable(); // رقم الهاتف (Phone Number)
            $table->string('fax_number')->nullable(); // رقم الفاكس (Fax Number)
            $table->string('mobile')->nullable(); // الجوال (Mobile)

            // Account Information (FK to accounts table)
            $table->foreignId('sales_account_id')->nullable()->constrained('accounts')->nullOnDelete(); // حساب المبيعات (Sales Account)
            $table->foreignId('purchase_account_id')->nullable()->constrained('accounts')->nullOnDelete(); // حساب المشتريات (Purchase Account)

            // Legacy fields for backward compatibility
            $table->string('location')->nullable(); // الموقع (Location) - legacy field
            $table->foreignId('department_warehouse_id')->nullable()->constrained('department_warehouses')->nullOnDelete(); // القسم

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
            $table->index(['company_id', 'warehouse_number']);
            $table->index(['warehouse_keeper_id']);
            $table->index(['sales_account_id']);
            $table->index(['purchase_account_id']);
            $table->unique(['company_id', 'warehouse_number']);
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
