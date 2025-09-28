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
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            
            // User and Company Relations
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            
            // Donor Information
            $table->string('donor_number', 50)->unique(); // رقم المانح
            $table->string('donor_name_ar'); // اسم المانح بالعربية
            $table->string('donor_name_en')->nullable(); // اسم المانح بالإنجليزية
            $table->string('donor_code', 50)->nullable(); // كود المانح
            
            // Contact Information
            $table->string('contact_person')->nullable(); // الشخص المسؤول
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('website')->nullable();
            
            // Address Information
            $table->text('address')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            
            // Donor Type and Category
            $table->enum('donor_type', ['individual', 'organization', 'government', 'international'])->default('organization');
            $table->enum('category', ['major', 'medium', 'minor'])->default('medium');
            
            // Financial Information
            $table->decimal('total_donations', 15, 2)->default(0); // إجمالي التبرعات
            $table->decimal('current_year_donations', 15, 2)->default(0); // تبرعات السنة الحالية
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->date('first_donation_date')->nullable(); // تاريخ أول تبرع
            $table->date('last_donation_date')->nullable(); // تاريخ آخر تبرع
            
            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'donor_type']);
            $table->index(['company_id', 'category']);
            $table->unique(['company_id', 'donor_number']);
            $table->unique(['company_id', 'donor_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donors');
    }
};
