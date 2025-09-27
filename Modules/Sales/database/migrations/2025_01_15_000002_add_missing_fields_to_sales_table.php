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
            
            // Add foreign key constraints that are missing
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
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
            // Drop foreign keys first
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['customer_id']);
            
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
