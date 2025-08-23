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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('company_name');
            $table->string('commercial_registration_number')->unique();
            $table->foreignId('company_type')->references('id')->on('company_types')->cascadeOnDelete();
            $table->foreignId('work_type')->references('id')->on('work_types')->cascadeOnDelete();
            $table->string('company_address');
            $table->string('company_logo')->nullable();
            $table->string('email')->unique();
            $table->string('country_code', 10);
            $table->string('phone');
            $table->boolean('allow_emails')->default(false);
            $table->decimal('income_tax_rate', 5, 2);
            $table->decimal('vat_rate', 5, 2);
            $table->year('fiscal_year');
            $table->date('from');
            $table->date('to');
            $table->foreignId('currency_id')->references('id')->on('currencies')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
