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
        Schema::create('manufacturing_process_raw_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manufacturing_process_id')->constrained('manufacturing_processes')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Item Information
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('item_number')->nullable();
            $table->string('item_name')->nullable();
            $table->text('item_description')->nullable();
            
            // Unit Information
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('unit_name')->nullable();
            
            // Warehouse Information
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->string('warehouse_name')->nullable();
            
            // Quantity Information
            $table->decimal('consumed_quantity', 15, 4)->default(0); // Required quantity
            $table->decimal('available_quantity', 15, 4)->default(0); // Available in warehouse
            $table->decimal('reserved_quantity', 15, 4)->default(0); // Reserved for this process
            $table->decimal('actual_consumed_quantity', 15, 4)->nullable(); // Actually consumed
            $table->decimal('shortage_quantity', 15, 4)->default(0); // Shortage amount
            
            // Cost Information
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('actual_unit_cost', 15, 2)->nullable();
            $table->decimal('actual_total_cost', 15, 2)->nullable();
            
            // Status Information
            $table->enum('status', ['available', 'insufficient', 'reserved', 'consumed'])->default('available');
            $table->boolean('is_available')->default(true);
            $table->boolean('is_critical')->default(false);
            
            // Additional Information
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            
            // System Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for Performance
            $table->index(['manufacturing_process_id', 'item_id']);
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'status']);
            $table->index(['is_available']);
            $table->index(['is_critical']);
            $table->index(['batch_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturing_process_raw_materials');
    }
};
