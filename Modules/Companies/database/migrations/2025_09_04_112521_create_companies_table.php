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

            $table->foreignId('user_id')->constrained('users')->onDelete('set null');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('set null');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('set null');
            $table->foreignId('industry_id')->constrained('industries')->onDelete('set null');
            $table->foreignId('business_type_id')->constrained('business_types')->onDelete('set null');
            $table->foreignId('country_id')->constrained('countries')->onDelete('set null');
            $table->foreignId('region_id')->constrained('regions')->onDelete('set null');
            $table->foreignId('city_id')->constrained('cities')->onDelete('set null');

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

            $table->foreignId('created_by')->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->constrained('users')->onDelete('set null');

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
