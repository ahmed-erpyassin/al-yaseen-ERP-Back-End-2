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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financial_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();

            $table->unsignedBigInteger('item_id');
            $table->string('description')->nullable();
            $table->decimal('quantity', 15, 2);
            $table->unsignedBigInteger('unit_id');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);

            $table->foreignId('tax_id')->nullable()->constrained('tax_rates')->nullOnDelete();
            $table->decimal('total_foregin', 15, 2)->default(0);
            $table->decimal('total_local', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
