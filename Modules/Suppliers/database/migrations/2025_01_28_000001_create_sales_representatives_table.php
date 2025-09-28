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
        Schema::create('sales_representatives', function (Blueprint $table) {
            $table->id();
            
            // User and Company Relations
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            
            // Sales Representative Information
            $table->string('representative_number', 50)->unique(); // رقم مندوب المبيعات
            $table->string('employee_number', 50)->nullable(); // رقم الموظف
            $table->string('first_name'); // الاسم الأول
            $table->string('last_name'); // الاسم الأخير
            $table->string('full_name')->nullable(); // الاسم الكامل
            
            // Contact Information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('emergency_contact')->nullable(); // جهة الاتصال في حالات الطوارئ
            
            // Address Information
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            
            // Employment Information
            $table->date('hire_date')->nullable(); // تاريخ التوظيف
            $table->date('termination_date')->nullable(); // تاريخ انتهاء الخدمة
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'freelance'])->default('full_time');
            $table->decimal('base_salary', 15, 2)->default(0); // الراتب الأساسي
            $table->decimal('commission_rate', 5, 2)->default(0); // نسبة العمولة
            
            // Sales Performance
            $table->decimal('sales_target', 15, 2)->default(0); // هدف المبيعات
            $table->decimal('current_sales', 15, 2)->default(0); // المبيعات الحالية
            $table->decimal('total_commission', 15, 2)->default(0); // إجمالي العمولات
            $table->integer('customers_count')->default(0); // عدد العملاء
            $table->integer('suppliers_count')->default(0); // عدد الموردين
            
            // Territory and Specialization
            $table->json('territory')->nullable(); // المنطقة الجغرافية المسؤول عنها
            $table->json('specialization')->nullable(); // التخصص (أنواع المنتجات)
            $table->text('notes')->nullable();
            
            // Status
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'department_id']);
            $table->index(['employment_type']);
            $table->unique(['company_id', 'representative_number']);
            $table->unique(['company_id', 'employee_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_representatives');
    }
};
