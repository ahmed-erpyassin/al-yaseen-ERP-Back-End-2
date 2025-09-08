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
        Schema::create('financial_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('company_id')->nullable()->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->unique();

            $table->foreignId('default_currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('vat_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('retained_earnings_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('rounding_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('default_sales_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('default_purchase_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_settings');
    }
};
