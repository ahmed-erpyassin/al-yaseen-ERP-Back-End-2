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
            // Add serial number field for table display
            if (!Schema::hasColumn('sales_items', 'serial_number')) {
                $table->integer('serial_number')->default(1)->after('sale_id');
            }

            // Add discount amount field for better calculation
            if (!Schema::hasColumn('sales_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_rate');
            }

            // Add discount percentage field
            if (!Schema::hasColumn('sales_items', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('discount_amount');
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
            if (Schema::hasColumn('sales_items', 'serial_number')) {
                $table->dropColumn('serial_number');
            }

            if (Schema::hasColumn('sales_items', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }

            if (Schema::hasColumn('sales_items', 'discount_percentage')) {
                $table->dropColumn('discount_percentage');
            }
        });
    }
};
