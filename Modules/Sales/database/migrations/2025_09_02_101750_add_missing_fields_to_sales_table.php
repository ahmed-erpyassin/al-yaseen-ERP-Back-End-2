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
        Schema::table('sales', function (Blueprint $table) {
            // Add missing fields for incoming orders
            $table->string('book_code', 50)->nullable()->after('id'); // Book code (sequential)
            $table->date('date')->nullable()->after('time'); // Date field
            $table->string('customer_email', 150)->nullable()->after('customer_id'); // Customer email
            $table->string('licensed_operator', 255)->nullable()->after('customer_email'); // Licensed operator
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('allowed_discount'); // Discount percentage
            $table->boolean('is_tax_inclusive')->default(false)->after('tax_percentage'); // Tax inclusive flag
            
            // Add foreign key constraints that are missing (skip employees for now)
            // Note: Most foreign keys are already defined in the main table creation
        });

        // Add indexes for better performance
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'book_code']);
            $table->index(['company_id', 'invoice_number']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['company_id', 'type']);
            $table->dropIndex(['company_id', 'book_code']);
            $table->dropIndex(['company_id', 'invoice_number']);
            $table->dropIndex(['company_id', 'customer_id']);
            $table->dropIndex(['company_id', 'date']);

            // Drop columns
            $table->dropColumn([
                'book_code',
                'date',
                'customer_email',
                'licensed_operator',
                'discount_percentage',
                'is_tax_inclusive'
            ]);
        });
    }
};
