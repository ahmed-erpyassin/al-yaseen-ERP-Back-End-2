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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('company_id')->nullable()->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->unique();

            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->decimal('rate', 8, 2);
            $table->enum('type', ['vat', 'withholding', 'custom'])->default('vat');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
