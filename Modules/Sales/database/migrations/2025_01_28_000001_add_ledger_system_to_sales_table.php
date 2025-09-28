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
            // Add ledger system fields for Sales Return Invoice
            $table->string('ledger_code', 50)->nullable()->after('book_code'); // Ledger code (sequential)
            $table->integer('ledger_number')->nullable()->after('ledger_code'); // Current ledger number
            $table->integer('ledger_invoice_count')->default(0)->after('ledger_number'); // Count of invoices in current ledger
            
            // Ensure customer_number field exists for dropdown functionality
            if (!Schema::hasColumn('sales', 'customer_number')) {
                $table->string('customer_number', 50)->nullable()->after('customer_id');
            }
            
            // Ensure customer_name field exists for dropdown functionality  
            if (!Schema::hasColumn('sales', 'customer_name')) {
                $table->string('customer_name', 255)->nullable()->after('customer_number');
            }
        });

        // Add indexes for better performance on ledger queries
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['company_id', 'type', 'ledger_number']);
            $table->index(['company_id', 'ledger_code']);
            $table->index(['company_id', 'customer_number']);
            $table->index(['company_id', 'customer_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['company_id', 'type', 'ledger_number']);
            $table->dropIndex(['company_id', 'ledger_code']);
            $table->dropIndex(['company_id', 'customer_number']);
            $table->dropIndex(['company_id', 'customer_name']);
            
            // Drop columns
            $table->dropColumn([
                'ledger_code',
                'ledger_number', 
                'ledger_invoice_count',
                'customer_number',
                'customer_name'
            ]);
        });
    }
};
