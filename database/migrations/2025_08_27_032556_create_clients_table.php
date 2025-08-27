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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('client_type', ['0', '1'])->default(0);

            $table->string('client_number')->unique();
            $table->string('company_name');
            $table->string('first_name');
            $table->string('second_name');
            $table->string('phone', 50);
            $table->string('mobile', 50);
            $table->string('address1', 255);
            $table->string('address2', 255);
            $table->string('city', 100);
            $table->string('region', 100);
            $table->string('postal_code', 50);
            $table->string('licensed_operator', 255);

            $table->string('code_number')->unique();
            $table->string('invoice_method', 100);
            $table->foreignId('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreignId('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->foreignId('funder_id')->references('id')->on('funders')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();


            $table->string('email', 255);
            $table->string('category', 100);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
