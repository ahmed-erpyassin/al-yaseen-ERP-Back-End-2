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
        Schema::table('purchase_items', function (Blueprint $table) {
            // Add account information for expense items
            
            // Account ID (foreign key to accounts table)
            if (!Schema::hasColumn('purchase_items', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('item_id')->constrained('accounts')->nullOnDelete();
            }
            
            // Account number (from accounts table)
            if (!Schema::hasColumn('purchase_items', 'account_number')) {
                $table->string('account_number', 50)->nullable()->after('account_id');
            }
            
            // Account name (from accounts table)
            if (!Schema::hasColumn('purchase_items', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }
            
            // Add indexes for better performance
            if (!Schema::hasIndex('purchase_items', ['account_id'])) {
                $table->index(['account_id']);
            }
            
            if (!Schema::hasIndex('purchase_items', ['account_number'])) {
                $table->index(['account_number']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['account_id']);
            
            // Drop indexes
            $table->dropIndex(['account_id']);
            $table->dropIndex(['account_number']);
            
            // Drop columns
            $table->dropColumn([
                'account_id',
                'account_number',
                'account_name',
            ]);
        });
    }
};
