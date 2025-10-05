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
        // Add new columns if they don't exist
        Schema::table('sales_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_items', 'serial_number')) {
                $table->integer('serial_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('sales_items', 'item_number')) {
                $table->string('item_number', 100)->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('sales_items', 'item_name')) {
                $table->string('item_name', 255)->nullable()->after('item_number');
            }
            if (!Schema::hasColumn('sales_items', 'unit_name')) {
                $table->string('unit_name', 100)->nullable()->after('description');
            }
            if (!Schema::hasColumn('sales_items', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_rate');
            }
            if (!Schema::hasColumn('sales_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            }
            if (!Schema::hasColumn('sales_items', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('unit_name');
            }
            if (!Schema::hasColumn('sales_items', 'notes')) {
                $table->text('notes')->nullable()->after('warehouse_id');
            }
        });

        // Update column types to match referenced tables
        Schema::table('sales_items', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->change();
            $table->unsignedBigInteger('item_id')->change();
        });

        // Add foreign key constraints if they don't exist
        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key already exists, ignore
        }

        // Add indexes for better performance
        try {
            Schema::table('sales_items', function (Blueprint $table) {
                $table->index(['sale_id', 'item_id']);
                $table->index('serial_number');
                $table->index('item_number');
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
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['item_id']);
            $table->dropForeign(['warehouse_id']);

            // Drop indexes
            $table->dropIndex(['sale_id', 'item_id']);
            $table->dropIndex(['serial_number']);
            $table->dropIndex(['item_number']);

            // Drop columns
            $table->dropColumn([
                'serial_number',
                'item_number',
                'item_name',
                'unit_name',
                'discount_percentage',
                'discount_amount',
                'warehouse_id',
                'notes'
            ]);
        });
    }
};
