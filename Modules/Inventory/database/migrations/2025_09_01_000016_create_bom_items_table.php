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
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // Parent item (finished product)
            $table->foreignId('component_id')->constrained('items')->cascadeOnDelete(); // Component item (raw material/sub-assembly)
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete(); // Unit of measurement
            
            // BOM Information
            $table->decimal('quantity', 12, 6); // Required quantity of component per unit of parent item
            
            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'item_id']);
            $table->index(['company_id', 'component_id']);
            $table->index(['company_id', 'branch_id']);
            $table->unique(['item_id', 'component_id']); // Prevent duplicate components in same BOM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
