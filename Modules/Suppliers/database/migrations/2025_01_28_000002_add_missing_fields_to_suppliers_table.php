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
        Schema::table('suppliers', function (Blueprint $table) {
            // Add missing fields that don't exist yet
            
            // Supplier Type and Number
            if (!Schema::hasColumn('suppliers', 'supplier_type')) {
                $table->enum('supplier_type', ['individual', 'business'])->default('business')->after('supplier_code');
            }
            
            if (!Schema::hasColumn('suppliers', 'supplier_number')) {
                $table->string('supplier_number', 50)->unique()->nullable()->after('supplier_code');
            }
            
            // Balance and Last Transaction Date
            if (!Schema::hasColumn('suppliers', 'balance')) {
                $table->decimal('balance', 15, 2)->default(0)->after('credit_limit');
            }
            
            if (!Schema::hasColumn('suppliers', 'last_transaction_date')) {
                $table->date('last_transaction_date')->nullable()->after('balance');
            }
            
            // Account Data
            if (!Schema::hasColumn('suppliers', 'code_number')) {
                $table->string('code_number', 50)->nullable()->after('supplier_number');
            }
            
            if (!Schema::hasColumn('suppliers', 'barcode_type_id')) {
                $table->foreignId('barcode_type_id')->nullable()->constrained('barcode_types')->nullOnDelete()->after('code_number');
            }
            
            // Foreign Key Relations
            if (!Schema::hasColumn('suppliers', 'department_id')) {
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('branch_id');
            }
            
            if (!Schema::hasColumn('suppliers', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->after('department_id');
            }
            
            if (!Schema::hasColumn('suppliers', 'donor_id')) {
                $table->foreignId('donor_id')->nullable()->constrained('donors')->nullOnDelete()->after('project_id');
            }
            
            if (!Schema::hasColumn('suppliers', 'sales_representative_id')) {
                $table->foreignId('sales_representative_id')->nullable()->constrained('sales_representatives')->nullOnDelete()->after('donor_id');
            }
            
            // Classification
            if (!Schema::hasColumn('suppliers', 'classification')) {
                $table->enum('classification', ['major', 'medium', 'minor'])->default('medium')->after('sales_representative_id');
            }
            
            if (!Schema::hasColumn('suppliers', 'custom_classification')) {
                $table->string('custom_classification')->nullable()->after('classification');
            }
        });

        // Add foreign key constraints for existing fields (simplified)
        Schema::table('suppliers', function (Blueprint $table) {
            // Add foreign key constraints (will skip if they already exist)
            try {
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }

            try {
                $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }

            try {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }

            try {
                $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }

            try {
                $table->foreign('region_id')->references('id')->on('regions')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }

            try {
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            } catch (\Exception) {
                // Foreign key already exists, skip
            }
        });

        // Add indexes for better performance
        Schema::table('suppliers', function (Blueprint $table) {
            $table->index(['company_id', 'supplier_number']);
            $table->index(['company_id', 'supplier_type']);
            $table->index(['company_id', 'classification']);
            $table->index(['company_id', 'last_transaction_date']);
            $table->index(['sales_representative_id']);
            $table->index(['department_id']);
            $table->index(['project_id']);
            $table->index(['donor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['company_id', 'supplier_number']);
            $table->dropIndex(['company_id', 'supplier_type']);
            $table->dropIndex(['company_id', 'classification']);
            $table->dropIndex(['company_id', 'last_transaction_date']);
            $table->dropIndex(['sales_representative_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['donor_id']);
            
            // Drop foreign key constraints
            $table->dropForeign(['barcode_type_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['project_id']);
            $table->dropForeign(['donor_id']);
            $table->dropForeign(['sales_representative_id']);
            
            // Drop columns
            $table->dropColumn([
                'supplier_type',
                'supplier_number',
                'balance',
                'last_transaction_date',
                'code_number',
                'barcode_type_id',
                'department_id',
                'project_id',
                'donor_id',
                'sales_representative_id',
                'classification',
                'custom_classification'
            ]);
        });
    }


};
