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
        Schema::table('project_resources', function (Blueprint $table) {
            // Add supplier relationship
            $table->foreignId('supplier_id')->nullable()->after('project_id')->constrained('suppliers')->nullOnDelete()->comment('Reference to supplier from suppliers table');

            // supplier_number, supplier_name removed - available via supplier relationship
            // project_number, project_name removed - available via project relationship

            // Modify allocation field to be decimal for percentage calculation
            $table->decimal('allocation_percentage', 5, 2)->nullable()->after('role')->comment('Allocation percentage (0-100)');

            // Add allocation value field for monetary calculations
            $table->decimal('allocation_value', 15, 2)->nullable()->after('allocation_percentage')->comment('Allocation value in currency');

            // Add notes field
            $table->text('notes')->nullable()->after('allocation_value')->comment('Additional notes for the resource');

            // Add status field for resource management
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active')->after('notes')->comment('Resource status');

            // Add resource type field
            $table->enum('resource_type', ['supplier', 'internal', 'contractor', 'consultant'])->default('supplier')->after('status')->comment('Type of resource');

            // Add indexes for better performance
            $table->index(['supplier_id']);
            $table->index(['resource_type', 'status']);
            $table->index(['project_id', 'supplier_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_resources', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['resource_type', 'status']);
            $table->dropIndex(['project_id', 'supplier_id']);

            // Drop foreign key constraint
            $table->dropForeign(['supplier_id']);

            // Drop columns
            $table->dropColumn([
                'supplier_id',
                'allocation_percentage',
                'allocation_value',
                'notes',
                'status',
                'resource_type'
            ]);
        });
    }
};
