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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Basic Supplier Information
            $table->string('supplier_name_ar'); // اسم المورد
            $table->string('supplier_name_en')->nullable(); // Supplier Name (English)
            $table->string('supplier_code')->nullable(); // كود المورد
            $table->string('contact_person')->nullable(); // الشخص المسؤول
            
            // Contact Information
            $table->string('phone')->nullable(); // الهاتف
            $table->string('mobile')->nullable(); // الجوال
            $table->string('email')->nullable(); // البريد الإلكتروني
            $table->string('website')->nullable(); // الموقع الإلكتروني
            $table->text('address')->nullable(); // العنوان
            
            // Financial Information
            $table->string('tax_number')->nullable(); // الرقم الضريبي
            $table->string('commercial_register')->nullable(); // السجل التجاري
            $table->decimal('credit_limit', 15, 2)->default(0); // حد الائتمان
            $table->integer('payment_terms')->default(0); // شروط الدفع (بالأيام)
            
            // Additional Information
            $table->text('notes')->nullable(); // ملاحظات
            $table->boolean('active')->default(true); // نشط

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'active']);
            $table->unique(['company_id', 'supplier_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
