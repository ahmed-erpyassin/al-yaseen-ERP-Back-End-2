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
        Schema::table('customers', function (Blueprint $table) {
            // Add customer type (Individual or Business)
            $table->enum('customer_type', ['individual', 'business'])->default('business')->after('customer_number');

            // Add balance field
            $table->decimal('balance', 15, 2)->default(0.00)->after('customer_type');

            // Add barcode fields
            $table->string('barcode')->nullable()->after('code');
            $table->string('barcode_type')->default('C128')->after('barcode');

            // Add foreign key constraints that were missing
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->foreign('region_id')->references('id')->on('regions')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['country_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['city_id']);

            // Drop added columns
            $table->dropColumn(['customer_type', 'balance', 'barcode', 'barcode_type']);
        });
    }
};
