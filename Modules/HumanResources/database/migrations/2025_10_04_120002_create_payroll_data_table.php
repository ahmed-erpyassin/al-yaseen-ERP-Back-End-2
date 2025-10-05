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
        Schema::create('payroll_data', function (Blueprint $table) {
            $table->id();

            // Core relationships
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('fiscal_year_id');

            // Payroll and Employee relationships
            $table->foreignId('payroll_record_id')->constrained('payroll_records')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            // Employee information (copied from employee record but editable)
            $table->string('employee_number')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('national_id')->nullable();
            $table->string('marital_status')->nullable(); // single, married
            $table->string('job_title')->nullable();
            $table->string('duration')->nullable(); // work duration/period

            // Salary information
            $table->decimal('basic_salary', 15, 2)->default(0.00);
            $table->decimal('income_tax', 15, 2)->default(0.00);
            $table->decimal('salary_for_payment', 15, 2)->default(0.00); // basic_salary - income_tax
            $table->decimal('paid_in_cash', 15, 2)->default(0.00);

            // Additional fields for calculations
            $table->decimal('allowances', 15, 2)->default(0.00);
            $table->decimal('deductions', 15, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->default(0.00);
            $table->decimal('overtime_rate', 10, 2)->default(0.00);
            $table->decimal('overtime_amount', 15, 2)->default(0.00);

            // Status and notes
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['payroll_record_id', 'employee_id']);
            $table->index(['company_id', 'employee_id']);
            $table->index(['status']);

            // Unique constraint to prevent duplicate employee entries per payroll record
            $table->unique(['payroll_record_id', 'employee_id'], 'unique_payroll_employee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_data');
    }
};
