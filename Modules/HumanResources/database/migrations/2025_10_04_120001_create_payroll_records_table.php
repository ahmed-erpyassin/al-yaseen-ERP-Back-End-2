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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            
            // Core relationships
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('fiscal_year_id');
            
            // Payroll specific fields
            $table->string('payroll_number')->unique();
            $table->date('date');
            $table->date('second_date')->nullable();
            
            // Currency information
            $table->unsignedBigInteger('currency_id');
            $table->decimal('currency_rate', 10, 4)->default(1.0000);
            
            // Account information
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            
            // Payment details
            $table->string('payment_account')->nullable();
            $table->string('salaries_wages_period')->nullable(); // "Salaries and wages for March / 2025"
            
            // Calculated totals
            $table->decimal('total_salaries', 15, 2)->default(0.00);
            $table->decimal('total_income_tax_deductions', 15, 2)->default(0.00);
            $table->decimal('total_payable_amount', 15, 2)->default(0.00);
            $table->decimal('total_salaries_paid_cash', 15, 2)->default(0.00);
            
            // Status and notes
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
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
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('cascade');
            
            // Indexes
            $table->index(['company_id', 'date']);
            $table->index(['payroll_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
