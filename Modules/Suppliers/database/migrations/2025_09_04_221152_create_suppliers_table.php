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

            // ✅ User and Company Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();

            // ✅ Location Information
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('region_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();

            // ✅ Basic Supplier Information (from Inventory migration)
            $table->string('supplier_name_ar'); // اسم المورد
            $table->string('supplier_name_en')->nullable(); // Supplier Name (English)
            $table->string('supplier_code')->nullable(); // كود المورد
            $table->string('contact_person')->nullable(); // الشخص المسؤول

            // ✅ Personal Names (existing fields)
            $table->string('first_name')->nullable();
            $table->string('second_name')->nullable();
            $table->string('contact_name')->nullable();

            // ✅ Contact Information (enhanced)
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website')->nullable(); // من migration الـ Inventory

            // ✅ Address Information (enhanced)
            $table->string('address_one')->nullable();
            $table->string('address_two')->nullable();
            $table->text('address')->nullable(); // العنوان الكامل من migration الـ Inventory
            $table->string('postal_code')->nullable();

            // ✅ Financial Information (من migration الـ Inventory)
            $table->string('tax_number')->nullable();
            $table->string('commercial_register')->nullable(); // السجل التجاري
            $table->decimal('credit_limit', 15, 2)->default(0); // حد الائتمان
            $table->integer('payment_terms')->default(0); // شروط الدفع (بالأيام)

            // ✅ Additional Information
            $table->text('notes')->nullable();

            // ✅ Status (enhanced)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('active')->default(true); // من migration الـ Inventory

            // ✅ Audit Fields (enhanced)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            // ✅ Indexes (من migration الـ Inventory)
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
