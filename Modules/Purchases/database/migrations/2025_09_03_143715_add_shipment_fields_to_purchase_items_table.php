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
            // Add shipment number field for incoming shipments
            if (!Schema::hasColumn('purchase_items', 'shipment_number')) {
                $table->string('shipment_number', 50)->nullable()->after('id');
            }
            
            // Add warehouse number field for inventory tracking
            if (!Schema::hasColumn('purchase_items', 'warehouse_number')) {
                $table->string('warehouse_number', 50)->nullable()->after('shipment_number');
            }
            
            // Add warehouse_id foreign key for proper relationship (skip foreign key for now)
            if (!Schema::hasColumn('purchase_items', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('warehouse_number');
            }
        });

        // Add indexes for better performance
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->index(['shipment_number']);
            $table->index(['warehouse_number']);
            $table->index(['warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['shipment_number']);
            $table->dropIndex(['warehouse_number']);
            $table->dropIndex(['warehouse_id']);
            
            // Drop foreign key constraints
            $table->dropForeign(['warehouse_id']);
            
            // Drop columns
            $table->dropColumn([
                'shipment_number',
                'warehouse_number',
                'warehouse_id'
            ]);
        });
    }
};
