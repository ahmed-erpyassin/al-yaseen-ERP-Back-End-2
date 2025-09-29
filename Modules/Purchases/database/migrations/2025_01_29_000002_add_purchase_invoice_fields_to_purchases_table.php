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
            // Add supplier number field for purchase invoices
            if (!Schema::hasColumn('purchases', 'supplier_number')) {
                $table->string('supplier_number', 50)->nullable()->after('supplier_name');
                $table->index('supplier_number');
            }
            
            // Add supplier email field for purchase invoices
            if (!Schema::hasColumn('purchases', 'supplier_email')) {
                $table->string('supplier_email', 150)->nullable()->after('supplier_number');
            }
            
            // Add supplier mobile field for purchase invoices
            if (!Schema::hasColumn('purchases', 'supplier_mobile')) {
                $table->string('supplier_mobile', 20)->nullable()->after('supplier_email');
            }
            
            // Add entry number field (from original table)
            if (!Schema::hasColumn('purchases', 'entry_number')) {
                $table->string('entry_number', 50)->nullable()->after('journal_number');
                $table->index('entry_number');
            }
            
            // Add purchase invoice number field (separate from invoice_number)
            if (!Schema::hasColumn('purchases', 'purchase_invoice_number')) {
                $table->string('purchase_invoice_number', 50)->nullable()->after('invoice_number');
                $table->index('purchase_invoice_number');
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
            if (Schema::hasColumn('purchases', 'supplier_number')) {
                $table->dropIndex(['supplier_number']);
            }
            if (Schema::hasColumn('purchases', 'entry_number')) {
                $table->dropIndex(['entry_number']);
            }
            if (Schema::hasColumn('purchases', 'purchase_invoice_number')) {
                $table->dropIndex(['purchase_invoice_number']);
            }
            
            // Drop columns
            $table->dropColumn([
                'supplier_number',
                'supplier_email', 
                'supplier_mobile',
                'entry_number',
                'purchase_invoice_number'
            ]);
        });
    }
};
