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
        Schema::table('purchases', function (Blueprint $table) {
            // Purchase Reference Invoice specific fields
            if (!Schema::hasColumn('purchases', 'purchase_reference_invoice_number')) {
                $table->string('purchase_reference_invoice_number', 50)->nullable()->after('expense_number');
            }
            
            if (!Schema::hasColumn('purchases', 'ledger_code')) {
                $table->string('ledger_code', 50)->nullable()->after('purchase_reference_invoice_number');
            }
            
            if (!Schema::hasColumn('purchases', 'affects_inventory')) {
                $table->boolean('affects_inventory')->default(false)->after('ledger_code');
            }
            
            if (!Schema::hasColumn('purchases', 'is_tax_applied_to_currency_rate')) {
                $table->boolean('is_tax_applied_to_currency_rate')->default(false)->after('is_tax_applied_to_currency');
            }
            
            if (!Schema::hasColumn('purchases', 'currency_rate_with_tax')) {
                $table->decimal('currency_rate_with_tax', 15, 6)->nullable()->after('currency_rate');
            }
            
            if (!Schema::hasColumn('purchases', 'ledger_invoice_count')) {
                $table->integer('ledger_invoice_count')->default(0)->after('ledger_code');
            }
            
            if (!Schema::hasColumn('purchases', 'journal_code')) {
                $table->string('journal_code', 50)->nullable()->after('journal_number');
            }
            
            if (!Schema::hasColumn('purchases', 'journal_invoice_count')) {
                $table->integer('journal_invoice_count')->default(0)->after('journal_code');
            }
        });

        // Add indexes for better performance
        Schema::table('purchases', function (Blueprint $table) {
            try {
                $table->index(['type', 'purchase_reference_invoice_number'], 'purchases_type_purchase_reference_invoice_number_index');
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['purchase_reference_invoice_number']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['ledger_code']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['affects_inventory']);
            } catch (\Exception $e) {
                // Index might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['type', 'purchase_reference_invoice_number']);
            $table->dropIndex(['purchase_reference_invoice_number']);
            $table->dropIndex(['ledger_code']);
            $table->dropIndex(['affects_inventory']);
            
            // Drop columns
            $table->dropColumn([
                'purchase_reference_invoice_number',
                'ledger_code',
                'affects_inventory',
                'is_tax_applied_to_currency_rate',
                'currency_rate_with_tax',
                'ledger_invoice_count',
                'journal_code',
                'journal_invoice_count'
            ]);
        });
    }


};
