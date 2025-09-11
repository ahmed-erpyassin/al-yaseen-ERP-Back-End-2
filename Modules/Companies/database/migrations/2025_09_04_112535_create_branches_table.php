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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // ✅ User and Company Relations
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();

            // ✅ Location Information
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();

            // ✅ Branch Information (enhanced with Inventory fields)
            $table->string('code', 50)->unique(); // Branch Code
            $table->string('branch_code')->nullable(); // كود الفرع (من migration الـ Inventory)
            $table->string('name', 150); // Branch Name
            $table->string('branch_name_ar'); // اسم الفرع بالعربية (من migration الـ Inventory)
            $table->string('branch_name_en')->nullable(); // اسم الفرع بالإنجليزية (من migration الـ Inventory)

            // ✅ Manager Information (enhanced)
            $table->string('manager_name')->nullable(); // اسم المدير (من migration الـ Inventory)

            // ✅ Contact Information (enhanced)
            $table->string('address', 255)->nullable(); // Address
            $table->text('address_full')->nullable(); // العنوان الكامل (من migration الـ Inventory)
            $table->string('landline', 50)->nullable(); // Landline
            $table->string('phone')->nullable(); // الهاتف (من migration الـ Inventory)
            $table->string('mobile', 50)->nullable(); // Mobile
            $table->string('email', 150)->nullable(); // Email

            // ✅ Additional Information
            $table->string('logo', 255)->nullable(); // Logo
            $table->string('tax_number', 100)->nullable(); // Tax Number
            $table->string('timezone', 50)->nullable(); // Timezone

            // ✅ Status Management (enhanced)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('active')->default(true); // نشط (من migration الـ Inventory)

            // ✅ Audit Fields (enhanced)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // ✅ Indexes (من migration الـ Inventory)
            $table->index(['company_id', 'active']);
            $table->unique(['company_id', 'branch_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
