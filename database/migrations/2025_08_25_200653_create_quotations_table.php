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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('user_Id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('quotation_number')->unique();
            $table->date('quotation_date');
            $table->date('expiry_date');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email');
            $table->string('license_number');
            $table->foreignId('currency_id')->references('id')->on('currencies')->cascadeOnDelete();
            $table->decimal('exchange_rate', 12, 4)->default(1);

            $table->decimal('allowed_discount', 12, 2)->default(0);
            $table->decimal('subtotal_without_tax', 12, 2)->default(0);
            $table->decimal('precentage', 12, 2)->default(0);
            $table->decimal('vat', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
