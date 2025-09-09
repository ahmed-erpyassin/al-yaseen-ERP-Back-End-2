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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('country_id');
            $table->unsignedBigInteger('region_id');
            $table->unsignedBigInteger('city_id');
            $table->string('first_name');
            $table->string('second_name');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone');
            $table->string('mobile');
            $table->string('address_one');
            $table->string('address_two');
            $table->string('postal_code');
            $table->string('tax_number');
            $table->string('notes');
            $table->foreignId('created_by')->constrained()->onDelete('cascade');
            $table->foreignId('updated_by')->constrained()->onDelete('cascade');
            $table->foreignId('deleted_by')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
