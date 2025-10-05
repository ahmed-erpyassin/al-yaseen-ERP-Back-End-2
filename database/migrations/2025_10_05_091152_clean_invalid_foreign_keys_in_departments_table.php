<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clean up invalid foreign key references before adding constraints

        // Set invalid budget_id to null
        DB::statement('UPDATE departments SET budget_id = NULL WHERE budget_id NOT IN (SELECT id FROM budgets)');

        // Set invalid parent_id to null
        DB::statement('UPDATE departments d1 SET parent_id = NULL WHERE parent_id NOT IN (SELECT id FROM departments d2 WHERE d2.id != d1.id)');

        // Set invalid company_id to null (though this should not happen)
        DB::statement('UPDATE departments SET company_id = NULL WHERE company_id NOT IN (SELECT id FROM companies)');

        // Set invalid branch_id to null
        DB::statement('UPDATE departments SET branch_id = NULL WHERE branch_id NOT IN (SELECT id FROM branches)');

        // Set invalid fiscal_year_id to null
        DB::statement('UPDATE departments SET fiscal_year_id = NULL WHERE fiscal_year_id NOT IN (SELECT id FROM fiscal_years)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse data cleanup
    }
};
