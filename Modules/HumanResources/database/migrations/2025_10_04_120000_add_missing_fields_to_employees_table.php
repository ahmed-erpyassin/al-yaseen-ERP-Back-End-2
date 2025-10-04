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
        Schema::table('employees', function (Blueprint $table) {
            // Add missing fields if they don't exist
            if (!Schema::hasColumn('employees', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('first_name');
            }
            
            if (!Schema::hasColumn('employees', 'students_count')) {
                $table->unsignedInteger('students_count')->default(0)->after('dependents_count');
            }
            
            if (!Schema::hasColumn('employees', 'currency_rate')) {
                $table->decimal('currency_rate', 10, 4)->default(1.0000)->after('currency_id');
            }
            
            if (!Schema::hasColumn('employees', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0.00)->after('monthly_discount');
            }
            
            if (!Schema::hasColumn('employees', 'category')) {
                $table->string('category', 100)->nullable()->after('job_title_id');
            }
            
            // Add foreign key constraints for existing fields if they don't exist
            // Note: We'll add these constraints carefully to avoid conflicts

            // Check if foreign keys already exist before adding them
            $foreignKeys = collect(DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'employees'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            "))->pluck('CONSTRAINT_NAME')->toArray();

            if (!in_array('employees_company_id_foreign', $foreignKeys)) {
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            }

            if (!in_array('employees_branch_id_foreign', $foreignKeys)) {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            }

            if (!in_array('employees_currency_id_foreign', $foreignKeys)) {
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            }

            if (!in_array('employees_user_id_foreign', $foreignKeys)) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }

            // Clean up invalid manager_id values first
            DB::statement('UPDATE employees SET manager_id = NULL WHERE manager_id NOT IN (SELECT id FROM (SELECT id FROM employees) as temp)');

            // Make manager_id nullable first, then add foreign key
            $table->unsignedBigInteger('manager_id')->nullable()->change();
            if (!in_array('employees_manager_id_foreign', $foreignKeys)) {
                $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['manager_id']);
            
            // Drop added columns
            $table->dropColumn([
                'last_name',
                'students_count',
                'currency_rate',
                'balance',
                'category'
            ]);
        });
    }
};
