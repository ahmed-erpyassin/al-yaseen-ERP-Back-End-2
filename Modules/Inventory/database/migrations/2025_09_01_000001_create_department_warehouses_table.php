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
        Schema::create('department_warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Department Information
            $table->string('department_number')->unique(); // رقم القسم
            $table->string('department_name_ar'); // اسم القسم بالعربية
            $table->string('department_name_en')->nullable(); // اسم القسم بالإنجليزية
            $table->text('description')->nullable(); // الوصف
            
            // Department Manager
            $table->string('manager_name')->nullable(); // اسم المدير
            $table->string('manager_phone')->nullable(); // هاتف المدير
            $table->string('manager_email')->nullable(); // بريد المدير الإلكتروني
            
            // Status
            $table->boolean('active')->default(true); // نشط
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'active']);
            $table->unique(['company_id', 'department_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_warehouses');
    }
};
