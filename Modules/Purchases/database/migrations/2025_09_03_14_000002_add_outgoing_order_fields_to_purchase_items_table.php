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
            // Add serial number for table display
            if (!Schema::hasColumn('purchase_items', 'serial_number')) {
                $table->integer('serial_number')->nullable()->after('purchase_id');
            }

            // Add item number and name fields
            if (!Schema::hasColumn('purchase_items', 'item_number')) {
                $table->string('item_number', 50)->nullable()->after('item_id');
            }
            if (!Schema::hasColumn('purchase_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_number');
            }

            // Add unit field
            if (!Schema::hasColumn('purchase_items', 'unit')) {
                $table->string('unit')->nullable()->after('description');
            }

            // Add discount fields
            if (!Schema::hasColumn('purchase_items', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_rate');
            }
            if (!Schema::hasColumn('purchase_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            }

            // Add total without tax
            if (!Schema::hasColumn('purchase_items', 'total_without_tax')) {
                $table->decimal('total_without_tax', 15, 2)->default(0)->after('discount_amount');
            }
        });

        // Add foreign key constraint and indexes in a separate schema call
        try {
            Schema::table('purchase_items', function (Blueprint $table) {
                // Add foreign key constraint for item_id (type already fixed in previous migration)
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, ignore the error
        }

        try {
            Schema::table('purchase_items', function (Blueprint $table) {
                // Add indexes for better performance
                $table->index(['purchase_id', 'item_id']);
                $table->index(['item_number']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['item_id']);
            
            // Drop indexes
            $table->dropIndex(['purchase_id', 'item_id']);
            $table->dropIndex(['item_number']);
            
            // Drop columns
            $table->dropColumn([
                'serial_number',
                'item_number',
                'item_name',
                'unit',
                'discount_percentage',
                'discount_amount',
                'total_without_tax'
            ]);
        });
    }
};
