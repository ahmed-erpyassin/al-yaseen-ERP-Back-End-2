<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Create Inventory Movement Data Table (Details).
     */
    public function up(): void
    {
        Schema::create('inventory_movement_data', function (Blueprint $table) {
            $table->id();
            
            // ✅ Company and Movement Reference
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('inventory_movement_id')->constrained('inventory_movements')->cascadeOnDelete(); // Reference to header table
            
            // ✅ Item Information (from Items table)
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // Reference to items table
            $table->string('item_number')->nullable(); // Store for quick access
            $table->string('item_name')->nullable(); // Store for quick access
            $table->text('item_description')->nullable(); // Store for quick access
            
            // ✅ Unit Information (from Units table)
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete(); // Reference to units table
            $table->string('unit_name')->nullable(); // Store for quick access
            $table->string('unit_code')->nullable(); // Store for quick access
            
            // ✅ Warehouse Information (from Warehouses table)
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete(); // Reference to warehouses table
            $table->string('warehouse_number')->nullable(); // Store for quick access
            $table->string('warehouse_name')->nullable(); // Store for quick access
            
            // ✅ Quantity Information
            $table->decimal('inventory_count', 15, 4)->default(0); // Current inventory count
            $table->decimal('quantity', 15, 4); // Movement quantity (+ for inbound, - for outbound)
            $table->decimal('previous_quantity', 15, 4)->default(0); // Quantity before movement
            $table->decimal('new_quantity', 15, 4)->default(0); // Quantity after movement
            
            // ✅ Pricing Information
            $table->decimal('unit_cost', 15, 4)->default(0); // Cost per unit
            $table->decimal('unit_price', 15, 4)->default(0); // Price per unit
            $table->decimal('total_cost', 15, 2)->default(0); // Total cost (quantity * unit_cost)
            $table->decimal('total_price', 15, 2)->default(0); // Total price (quantity * unit_price)
            
            // ✅ Additional Information
            $table->text('notes')->nullable();
            $table->string('batch_number')->nullable(); // Batch/Lot number
            $table->date('expiry_date')->nullable(); // Expiry date for items
            $table->string('serial_number')->nullable(); // Serial number for tracked items
            
            // ✅ Location Information
            $table->string('location_code')->nullable(); // Specific location within warehouse
            $table->string('shelf_number')->nullable(); // Shelf number
            $table->string('bin_number')->nullable(); // Bin number
            
            // ✅ System Fields            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ✅ Foreign Key Constraints (already handled by foreignId above)
            
            // ✅ Indexes
            $table->index(['company_id', 'inventory_movement_id']);
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['item_number']);
            $table->index(['warehouse_number']);
            $table->index(['batch_number']);
            $table->index(['serial_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movement_data');
    }
};
