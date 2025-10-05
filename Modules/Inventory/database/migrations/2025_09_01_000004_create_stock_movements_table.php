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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id')->nullable(); // Document reference
            $table->unsignedBigInteger('item_id'); // Changed from inventory_item_id (skip foreign key for now)
            $table->unsignedBigInteger('unit_id'); // Unit of measurement (skip foreign key for now)

            // Type with specific enum values from your schema
            $table->enum('type', ['sales', 'purchase', 'production', 'adjustments', 'transfer']);

            // Movement type (in/out)
            $table->enum('movement_type', ['in', 'out']);

            // Quantities and costs
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('total_cost', 12, 2)->nullable();

            // Transaction date and notes
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'branch_id']);
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'movement_type']);
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
