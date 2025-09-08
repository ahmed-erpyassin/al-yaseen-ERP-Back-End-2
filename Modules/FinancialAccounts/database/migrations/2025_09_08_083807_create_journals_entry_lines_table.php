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
        Schema::create('journals_entry_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('company_id')->nullable()->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->unique();

            $table->foreignId('journal_entry_id')->constrained('journals_entries')->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->nullOnDelete();
            $table->unsignedBigInteger('project_id')->nullable(); // لو في جدول مشاريع مستقبلاً

            $table->decimal('debit', 18, 6)->default(0);
            $table->decimal('credit', 18, 6)->default(0);
            $table->decimal('exchange_rate', 18, 6)->default(1);

            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals_entry_lines');
    }
};
