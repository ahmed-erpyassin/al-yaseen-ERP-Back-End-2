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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('notbook');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date')->nullable();
            $table->time('invoice_time')->nullable();
            $table->date('due_date')->nullable();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();

            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('currency_rate', 15, 4)->default(1);

            $table->text('notes')->nullable();

            $table->decimal('cash_paid', 18, 2)->default(0);
            $table->decimal('card_paid', 18, 2)->default(0);
            $table->foreignId('card_cash_currency')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('allowed_discount', 18, 2)->default(0);
            $table->decimal('subtotal_without_tax', 18, 2)->default(0);
            $table->decimal('vat', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('advance_paid', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_invoices');
    }
};
