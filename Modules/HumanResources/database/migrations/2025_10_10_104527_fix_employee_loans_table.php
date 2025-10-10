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
        Schema::table('employee_loans', function (Blueprint $table) {
            // Fix foreign key constraint
            $table->dropForeign(['employee_id']);
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();

            // Add missing columns
            $table->string('loan_number')->after('employee_id');
            $table->string('loan_type')->default('personal')->after('loan_number');
            $table->decimal('loan_amount', 10, 2)->after('loan_type');
            $table->decimal('interest_rate', 5, 2)->default(0)->after('loan_amount');
            $table->date('loan_date')->after('interest_rate');
            $table->integer('repayment_period')->after('loan_date');
            $table->decimal('total_paid', 10, 2)->default(0)->after('monthly_deduction');
            $table->decimal('remaining_balance', 10, 2)->after('total_paid');
            $table->string('purpose')->nullable()->after('status');
            $table->string('guarantor_name')->nullable()->after('purpose');
            $table->string('guarantor_phone')->nullable()->after('guarantor_name');
            $table->text('notes')->nullable()->after('guarantor_phone');

            // Rename columns
            $table->renameColumn('amount', 'old_amount');
            $table->renameColumn('remaining', 'old_remaining');
            $table->renameColumn('installments', 'old_installments');

            // Add soft deletes
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
