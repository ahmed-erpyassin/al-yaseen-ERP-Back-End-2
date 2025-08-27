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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_number')->unique();
            $table->string('name');

            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('account_nature', ['all', 'debit', 'credit']);

            $table->unsignedTinyInteger('level')->default(1);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();


            $table->enum('report_type', ['balance_sheet', 'income_statement', 'other'])->nullable();

            $table->boolean('allow_all_users')->default(true);
            $table->foreignId('allowed_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->date('opening_date')->nullable();
            $table->string('opened_by');

            $table->string('linked_account');
            $table->string('property_id');
            $table->decimal('depreciation_rate', 5, 2)->nullable();
            $table->enum('depreciation_classification', [
                'none',
                'pl',
                'trading',
                'operating',
                'income_expense'
            ])->default('none');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
