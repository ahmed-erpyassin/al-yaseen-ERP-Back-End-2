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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('financial_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->nullOnDelete();
            $table->foreignId('business_type_id')->nullable()->constrained('business_types')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();

            $table->string('title', 255);
            $table->string('commercial_registeration_number', 100);
            $table->string('address', 255)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('email', 150);
            $table->string('landline', 50)->nullable();
            $table->string('mobile', 50)->nullable();

            $table->decimal('income_tax_rate', 5, 2)->default(0.00);
            $table->decimal('vat_rate', 5, 2)->default(0.00);

            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
