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
            $table->foreignId('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('employee_number')->unique();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('second_name')->nullable();
            $table->string('third_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone1')->nullable();
            $table->string('phone2')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('iban')->nullable();
            $table->string('car_number')->nullable();
            $table->integer('children_count')->default(0);
            $table->integer('wives_count')->default(0);
            $table->integer('family_count')->default(0);
            $table->integer('dependents_count')->default(0);
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('id_number')->nullable();
            $table->string('job_title')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('employee_manager')->nullable();
            $table->string('employee_status')->nullable();
            $table->string('department')->nullable();
            $table->string('work_title')->nullable();
            $table->decimal('salary', 12, 2)->default(0);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('allowances', 12, 2)->default(0);
            $table->foreignId('currency')->references('id')->on('currencies')->cascadeOnDelete();
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
