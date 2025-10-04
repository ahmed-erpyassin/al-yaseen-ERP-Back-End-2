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
            // Add expense-specific fields
            
            // Expense number (sequential like OUT-0001)
            if (!Schema::hasColumn('purchases', 'expense_number')) {
                $table->string('expense_number', 50)->nullable()->after('outgoing_order_number');
            }
            
            // Supplier email for expenses
            if (!Schema::hasColumn('purchases', 'supplier_email')) {
                $table->string('supplier_email')->nullable()->after('supplier_name');
            }
            
            // Add indexes for expense functionality
            if (!Schema::hasIndex('purchases', ['type', 'expense_number'])) {
                $table->index(['type', 'expense_number']);
            }
            
            if (!Schema::hasIndex('purchases', ['expense_number'])) {
                $table->index(['expense_number']);
            }
            
            if (!Schema::hasIndex('purchases', ['supplier_id', 'type'])) {
                $table->index(['supplier_id', 'type']);
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
            $table->dropIndex(['type', 'expense_number']);
            $table->dropIndex(['expense_number']);
            $table->dropIndex(['supplier_id', 'type']);
            
            // Drop columns
            $table->dropColumn([
                'expense_number',
                'supplier_email',
            ]);
        });
    }
};
