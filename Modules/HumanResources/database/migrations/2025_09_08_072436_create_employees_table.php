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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('fiscal_year_id');
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('job_title_id')->constrained('job_titles')->cascadeOnDelete();
            $table->string('category', 100)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();

            $table->string('employee_number')->unique();
            $table->string('code')->unique();

            $table->string('nickname', 50)->nullable();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('second_name', 100)->nullable();
            $table->string('third_name', 100)->nullable();
            $table->string('phone1', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->string('email', 255)->unique();

            $table->date('birth_date');
            $table->string('address', 255);
            $table->string('national_id', 100);
            $table->string('id_number', 50);
            $table->enum('gender', ['male', 'female']);

            $table->unsignedInteger('wives_count')->default(0);
            $table->unsignedInteger('children_count')->default(0);
            $table->unsignedInteger('students_count')->default(0);
            $table->string('dependents_count', 50)->nullable();

            $table->string('car_number', 50)->nullable();
            $table->boolean('is_driver')->default(false);
            $table->boolean('is_sales')->default(false);

            $table->date('hire_date');
            $table->string('employee_code', 50);
            $table->string('employee_identifier', 50);
            $table->string('job_address', 255);

            $table->decimal('salary', 15, 2)->default(0.00);
            $table->decimal('billing_rate', 10, 2)->default(0.00);
            $table->decimal('monthly_discount', 10, 2)->default(0.00);
            $table->decimal('balance', 15, 2)->default(0.00);

            $table->unsignedBigInteger('currency_id');
            $table->decimal('currency_rate', 10, 4)->default(1.0000);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('fiscal_year_id')->references('id')->on('fiscal_years')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
