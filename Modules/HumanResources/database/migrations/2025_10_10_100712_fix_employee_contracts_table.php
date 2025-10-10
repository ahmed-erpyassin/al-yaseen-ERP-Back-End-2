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
        Schema::table('employee_contracts', function (Blueprint $table) {
            // Add missing columns
            $table->string('contract_type')->default('permanent')->after('contract_number');
            $table->decimal('salary', 10, 2)->after('basic_salary');
            $table->integer('working_hours')->default(8)->after('salary');
            $table->integer('vacation_days')->default(21)->after('working_hours');
            $table->text('terms_conditions')->nullable()->after('vacation_days');

            // Rename columns
            $table->renameColumn('statue', 'status');
            $table->renameColumn('deducations', 'deductions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->dropColumn(['contract_type', 'salary', 'working_hours', 'vacation_days', 'terms_conditions']);
            $table->renameColumn('status', 'statue');
            $table->renameColumn('deductions', 'deducations');
        });
    }
};
