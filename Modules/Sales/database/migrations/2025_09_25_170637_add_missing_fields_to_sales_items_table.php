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
            // Check if columns exist before adding them
            if (!Schema::hasColumn('sales_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('item_id');
                // Foreign key will be added later after units table is created
            }

            if (!Schema::hasColumn('sales_items', 'item_number')) {
                $table->string('item_number')->nullable()->after('item_id'); // Auto-generated item number
            }

            if (!Schema::hasColumn('sales_items', 'item_name')) {
                $table->string('item_name')->nullable()->after('item_id'); // Item name for display
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('sales_items', 'unit_id')) {
                $table->dropColumn('unit_id');
            }

            if (Schema::hasColumn('sales_items', 'item_number')) {
                $table->dropColumn('item_number');
            }

            if (Schema::hasColumn('sales_items', 'item_name')) {
                $table->dropColumn('item_name');
            }
        });
    }
};
