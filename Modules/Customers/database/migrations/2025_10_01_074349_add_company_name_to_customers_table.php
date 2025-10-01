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
            // Add company_name field if it doesn't exist
            if (!Schema::hasColumn('customers', 'company_name')) {
                $table->string('company_name')->nullable()->after('customer_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop company_name field if it exists
            if (Schema::hasColumn('customers', 'company_name')) {
                $table->dropColumn('company_name');
            }
        });
    }
};
