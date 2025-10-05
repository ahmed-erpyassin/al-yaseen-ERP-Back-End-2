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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->foreignId('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();

            // Project fields
            $table->string('code')->nullable(); // Auto-generated project code
            $table->string('project_number')->nullable(); // Manual project number
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'open', 'on-hold','cancelled', 'closed'])->default('draft');
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('project_value', 15, 2)->nullable(); // Project value field
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->decimal('progress', 5, 2)->default(0);

            // Customer Information (auto-populated from customer selection)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('licensed_operator')->nullable();

            // Currency and Pricing
            $table->decimal('currency_price', 15, 2)->nullable();
            $table->boolean('include_vat')->default(false);

            // Project Manager Information
            $table->string('project_manager_name')->nullable(); // In addition to manager_id

            // Additional fields
            $table->text('notes')->nullable(); // Separate from description
            $table->timestamp('project_date')->nullable(); // Auto-filled date
            $table->time('project_time')->nullable(); // Auto-filled time



            // System fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['manager_id']);
            $table->index(['customer_id']);
            $table->index(['currency_id']);
            $table->index(['country_id']);
            $table->index(['start_date', 'end_date']);
            $table->index(['code']);
            $table->index(['project_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
