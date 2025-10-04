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
        Schema::table('customers', function (Blueprint $table) {
            // Remove unnecessary columns
            if (Schema::hasColumn('customers', 'licensed_operator')) {
                $table->dropColumn('licensed_operator');
            }
            if (Schema::hasColumn('customers', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('customers', 'invoice_type')) {
                $table->dropColumn('invoice_type');
            }
            if (Schema::hasColumn('customers', 'category')) {
                $table->dropColumn('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Restore the columns if needed
            $table->string('licensed_operator')->nullable();
            $table->string('code')->nullable();
            $table->string('invoice_type')->nullable();
            $table->string('category')->nullable();
        });
    }
};
