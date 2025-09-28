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
        Schema::table('sales_items', function (Blueprint $table) {
            // Add account fields for service items
            if (!Schema::hasColumn('sales_items', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('sales_items', 'account_number')) {
                $table->string('account_number', 50)->nullable()->after('account_id');
            }
            if (!Schema::hasColumn('sales_items', 'account_name')) {
                $table->string('account_name', 150)->nullable()->after('account_number');
            }
            
            // Add tax rate fields for service items
            if (!Schema::hasColumn('sales_items', 'tax_rate_id')) {
                $table->unsignedBigInteger('tax_rate_id')->nullable()->after('tax_rate');
            }
            if (!Schema::hasColumn('sales_items', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 2)->default(0)->after('tax_rate_id');
            }
        });

        // Add foreign key constraints
        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        // Add indexes for better performance
        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->index('account_id');
                $table->index('account_number');
                $table->index('tax_rate_id');
            });
        } catch (\Exception $e) {
            // Indexes already exist, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['account_id']);
            $table->dropForeign(['tax_rate_id']);
            
            // Drop indexes
            $table->dropIndex(['account_id']);
            $table->dropIndex(['account_number']);
            $table->dropIndex(['tax_rate_id']);
            
            // Drop columns
            $table->dropColumn([
                'account_id',
                'account_number',
                'account_name',
                'tax_rate_id',
                'tax_amount'
            ]);
        });
    }
};
