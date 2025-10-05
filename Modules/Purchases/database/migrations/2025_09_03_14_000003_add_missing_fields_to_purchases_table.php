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
            // Add missing fields for incoming quotations
            
            // Quotation specific fields
            if (!Schema::hasColumn('purchases', 'quotation_number')) {
                $table->string('quotation_number', 50)->nullable()->after('journal_number');
            }
            
            if (!Schema::hasColumn('purchases', 'invoice_number')) {
                $table->string('invoice_number', 50)->nullable()->after('quotation_number');
            }
            
            if (!Schema::hasColumn('purchases', 'date')) {
                $table->date('date')->nullable()->after('invoice_number');
            }
            
            if (!Schema::hasColumn('purchases', 'time')) {
                $table->time('time')->nullable()->after('date');
            }
            
            if (!Schema::hasColumn('purchases', 'due_date')) {
                $table->date('due_date')->nullable()->after('time');
            }
            
            // Customer information (for incoming quotations)
            if (!Schema::hasColumn('purchases', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete()->after('supplier_id');
            }
            
            if (!Schema::hasColumn('purchases', 'customer_number')) {
                $table->string('customer_number', 50)->nullable()->after('customer_id');
            }
            
            if (!Schema::hasColumn('purchases', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_number');
            }
            
            if (!Schema::hasColumn('purchases', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }
            
            if (!Schema::hasColumn('purchases', 'customer_mobile')) {
                $table->string('customer_mobile', 20)->nullable()->after('customer_email');
            }
            
            // Supplier information
            if (!Schema::hasColumn('purchases', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('customer_mobile');
            }
            
            if (!Schema::hasColumn('purchases', 'licensed_operator')) {
                $table->string('licensed_operator')->nullable()->after('supplier_name');
            }
            
            // Ledger system (sequential numbering)
            if (!Schema::hasColumn('purchases', 'ledger_code')) {
                $table->string('ledger_code', 50)->nullable()->after('licensed_operator');
            }
            
            if (!Schema::hasColumn('purchases', 'ledger_number')) {
                $table->integer('ledger_number')->nullable()->after('ledger_code');
            }
            
            if (!Schema::hasColumn('purchases', 'ledger_invoice_count')) {
                $table->integer('ledger_invoice_count')->default(0)->after('ledger_number');
            }
            
            // Currency rate with tax consideration
            if (!Schema::hasColumn('purchases', 'currency_rate')) {
                $table->decimal('currency_rate', 15, 4)->nullable()->after('exchange_rate');
            }
            
            if (!Schema::hasColumn('purchases', 'currency_rate_with_tax')) {
                $table->decimal('currency_rate_with_tax', 15, 4)->nullable()->after('currency_rate');
            }
            
            if (!Schema::hasColumn('purchases', 'tax_rate_id')) {
                $table->foreignId('tax_rate_id')->nullable()->constrained('tax_rates')->nullOnDelete()->after('currency_rate_with_tax');
            }
            
            if (!Schema::hasColumn('purchases', 'is_tax_applied_to_currency')) {
                $table->boolean('is_tax_applied_to_currency')->default(false)->after('tax_rate_id');
            }
            
            // Discount fields (percentage and amount)
            if (!Schema::hasColumn('purchases', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('allowed_discount');
            }
            
            if (!Schema::hasColumn('purchases', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            }
            
            // Grand total field
            if (!Schema::hasColumn('purchases', 'grand_total')) {
                $table->decimal('grand_total', 15, 2)->default(0)->after('total_amount');
            }
            
            // Make some existing fields nullable
            $table->text('notes')->nullable()->change();
            $table->foreignId('deleted_by')->nullable()->change();
        });

        // Add indexes for better performance
        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'quotation_number']);
            $table->index(['company_id', 'invoice_number']);
            $table->index(['company_id', 'date']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['ledger_code', 'ledger_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['company_id', 'type']);
            $table->dropIndex(['company_id', 'quotation_number']);
            $table->dropIndex(['company_id', 'invoice_number']);
            $table->dropIndex(['company_id', 'date']);
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['company_id', 'supplier_id']);
            $table->dropIndex(['ledger_code', 'ledger_number']);
            
            // Drop foreign key constraints
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['tax_rate_id']);
            
            // Drop columns
            $table->dropColumn([
                'quotation_number',
                'invoice_number',
                'date',
                'time',
                'due_date',
                'customer_id',
                'customer_number',
                'customer_name',
                'customer_email',
                'customer_mobile',
                'supplier_name',
                'licensed_operator',
                'ledger_code',
                'ledger_number',
                'ledger_invoice_count',
                'currency_rate',
                'currency_rate_with_tax',
                'tax_rate_id',
                'is_tax_applied_to_currency',
                'discount_percentage',
                'discount_amount',
                'grand_total'
            ]);
        });
    }
};
