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
        Schema::create('incoming_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('notebook');
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->time('invoice_time')->nullable();
            $table->date('due_date')->nullable();

            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('currency')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('currency_price')->nullable();
            $table->boolean('include_tax');


            $table->decimal('allowed_discount', 12, 2)->default(0);
            $table->decimal('total_without_tax', 12, 2)->default(0);
            $table->decimal('tax_precentage', 12, 2)->default(0);
            $table->decimal('tax_value', 12, 2)->default(0);
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
        Schema::dropIfExists('incoming_orders');
    }
};
