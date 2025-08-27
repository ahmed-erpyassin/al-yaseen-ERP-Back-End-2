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
        Schema::create('debit_notes', function (Blueprint $table) {
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

            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->decimal('notice_amount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_notice_amount', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
