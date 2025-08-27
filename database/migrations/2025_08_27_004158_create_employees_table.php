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

            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('employee_number')->unique();

            $table->string('nickname', 50);
            $table->string('first_name', 100);
            $table->string('second_name', 100);
            $table->string('third_name', 100);
            $table->string('phone1', 20);
            $table->string('phone2', 20)->nullable();
            $table->string('email', 255)->unique();

            $table->date('birth_date');
            $table->string('address', 255);
            $table->string('national_id', 100);
            $table->string('id_number', 50);
            $table->enum('gender', ['ذكر', 'أنثى']);

            $table->unsignedInteger('wives_count')->default(0);
            $table->unsignedInteger('children_count')->default(0);
            $table->string('dependents_count', 50)->nullable();

            $table->string('car_number', 50)->nullable();
            $table->boolean('is_driver')->default(false);
            $table->boolean('is_sales')->default(false);

            $table->string('job_title', 255);
            $table->date('hiring_date');
            $table->string('employee_code', 50);
            $table->string('employee_identifier', 50);
            $table->string('job_address', 255);

            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('salary', 255);
            $table->decimal('billing_rate', 10, 2);
            $table->decimal('monthly_discount', 10, 2);

            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();

            $table->text('notes')->nullable();

            $table->timestamps();
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
