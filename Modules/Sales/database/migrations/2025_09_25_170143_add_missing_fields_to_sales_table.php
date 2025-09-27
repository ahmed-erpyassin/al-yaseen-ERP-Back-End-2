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
        Schema::table('sales', function (Blueprint $table) {
            // Add missing fields for the new quotation functionality
            $table->string('code')->nullable()->after('journal_id'); // Book code - sequential reference
            $table->date('date')->nullable()->after('invoice_number'); // Invoice date (auto-generated)
            $table->string('email')->nullable()->after('due_date'); // Customer email
            $table->string('licensed_operator')->nullable()->after('email'); // Licensed operator

            // Fix foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['customer_id']);

            // Drop added columns
            $table->dropColumn(['code', 'date', 'email', 'licensed_operator']);
        });
    }
};
