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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('currency_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('journal_id');
            $table->integer('journal_number');
            $table->enum('type', ['quotation', 'order', 'shipment', 'invoice', 'expense', 'return_invoice']);
            $table->enum('status', ['draft', 'approved', 'sent', 'invoiced', 'cancelled'])->default('draft');
            $table->decimal('cash_paid', 15, 2)->default(0);
            $table->decimal('checks_paid', 15, 2)->default(0);
            $table->decimal('allowed_discount', 15, 2)->default(0);
            $table->decimal('total_without_tax', 15, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->decimal('exchange_rate', 15, 4);
            $table->decimal('total_foreign', 15, 4);
            $table->decimal('total_local', 15, 4);
            $table->decimal('total_amount', 15, 4);
            $table->text('notes');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('deleted_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
