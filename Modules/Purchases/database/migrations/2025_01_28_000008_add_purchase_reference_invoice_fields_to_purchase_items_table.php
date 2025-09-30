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
            // Purchase Reference Invoice item specific fields
            if (!Schema::hasColumn('purchase_items', 'serial_number')) {
                $table->integer('serial_number')->nullable()->after('id');
            }
            
            if (!Schema::hasColumn('purchase_items', 'item_number')) {
                $table->string('item_number', 100)->nullable()->after('item_id');
            }
            
            if (!Schema::hasColumn('purchase_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_number');
            }
            
            if (!Schema::hasColumn('purchase_items', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('item_name')->constrained('units')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('purchase_items', 'unit_name')) {
                $table->string('unit_name')->nullable()->after('unit_id');
            }
            
            if (!Schema::hasColumn('purchase_items', 'first_selling_price')) {
                $table->decimal('first_selling_price', 15, 6)->nullable()->after('unit_price');
            }
            
            if (!Schema::hasColumn('purchase_items', 'affects_inventory')) {
                $table->boolean('affects_inventory')->default(false)->after('notes');
            }
        });

        // Add indexes for better performance
        Schema::table('purchase_items', function (Blueprint $table) {
            try {
                $table->index(['serial_number']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['item_number']);
            } catch (\Exception $e) {
                // Index might already exist
            }

            try {
                $table->index(['unit_id']);
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
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['unit_id']);
            
            // Drop indexes
            $table->dropIndex(['serial_number']);
            $table->dropIndex(['item_number']);
            $table->dropIndex(['unit_id']);
            $table->dropIndex(['affects_inventory']);
            
            // Drop columns
            $table->dropColumn([
                'serial_number',
                'item_number',
                'item_name',
                'unit_id',
                'unit_name',
                'first_selling_price',
                'affects_inventory'
            ]);
        });
    }


};
