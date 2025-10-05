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
            // Add missing fields for purchase items
            
            // Serial number for table display
            if (!Schema::hasColumn('purchase_items', 'serial_number')) {
                $table->integer('serial_number')->nullable()->after('id');
            }
            
            // Item number (from items table)
            if (!Schema::hasColumn('purchase_items', 'item_number')) {
                $table->string('item_number', 50)->nullable()->after('item_id');
            }
            
            // Item name (from items table)
            if (!Schema::hasColumn('purchase_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_number');
            }
            
            // Unit information (skip foreign key for now)
            if (!Schema::hasColumn('purchase_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('item_name');
            }
            
            if (!Schema::hasColumn('purchase_items', 'unit_name')) {
                $table->string('unit_name', 50)->nullable()->after('unit_id');
            }
            
            // Discount fields (percentage and amount)
            if (!Schema::hasColumn('purchase_items', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_rate');
            }
            
            if (!Schema::hasColumn('purchase_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            }
            
            // Net unit price after discount
            if (!Schema::hasColumn('purchase_items', 'net_unit_price')) {
                $table->decimal('net_unit_price', 15, 4)->default(0)->after('discount_amount');
            }
            
            // Line total before tax
            if (!Schema::hasColumn('purchase_items', 'line_total_before_tax')) {
                $table->decimal('line_total_before_tax', 15, 4)->default(0)->after('net_unit_price');
            }
            
            // Tax amount for this line
            if (!Schema::hasColumn('purchase_items', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 4)->default(0)->after('tax_rate');
            }
            
            // Line total after tax
            if (!Schema::hasColumn('purchase_items', 'line_total_after_tax')) {
                $table->decimal('line_total_after_tax', 15, 4)->default(0)->after('tax_amount');
            }
            
            // Notes for this item
            if (!Schema::hasColumn('purchase_items', 'notes')) {
                $table->text('notes')->nullable()->after('line_total_after_tax');
            }
        });

        // Add indexes for better performance (skip if already exists)
        Schema::table('purchase_items', function (Blueprint $table) {
            // Check if index doesn't exist before creating
            if (!Schema::hasIndex('purchase_items', 'purchase_items_purchase_id_item_id_index')) {
                $table->index(['purchase_id', 'item_id']);
            }
            if (!Schema::hasIndex('purchase_items', 'purchase_items_item_number_index')) {
                $table->index(['item_number']);
            }
            if (!Schema::hasIndex('purchase_items', 'purchase_items_unit_id_index')) {
                $table->index(['unit_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['purchase_id', 'item_id']);
            $table->dropIndex(['item_number']);
            $table->dropIndex(['unit_id']);
            
            // Drop foreign key constraints
            $table->dropForeign(['unit_id']);
            
            // Drop columns
            $table->dropColumn([
                'serial_number',
                'item_number',
                'item_name',
                'unit_id',
                'unit_name',
                'discount_percentage',
                'discount_amount',
                'net_unit_price',
                'line_total_before_tax',
                'tax_amount',
                'line_total_after_tax',
                'notes'
            ]);
        });
    }
};
