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
        Schema::table('purchases', function (Blueprint $table) {
            // Add customer relationship for outgoing orders
            $table->unsignedBigInteger('customer_id')->nullable()->after('supplier_id');
            
            // Add outgoing order specific fields
            $table->string('outgoing_order_number', 50)->nullable()->after('journal_number');
            $table->string('customer_number', 50)->nullable()->after('customer_id');
            $table->string('customer_name')->nullable()->after('customer_number');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_mobile', 20)->nullable()->after('customer_email');
            $table->string('licensed_operator')->nullable()->after('customer_mobile');
            
            // Add date and time fields
            $table->date('date')->nullable()->after('licensed_operator');
            $table->time('time')->nullable()->after('date');
            $table->date('due_date')->nullable()->after('time');
            
            // Add journal/ledger system fields
            $table->string('journal_code', 50)->nullable()->after('due_date');
            $table->integer('journal_invoice_count')->default(0)->after('journal_code');
            
            // Add discount fields
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('allowed_discount');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_percentage');
            
            // Add VAT activation field
            $table->boolean('is_tax_inclusive')->default(false)->after('tax_amount');
            
            // Add foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index(['type', 'company_id']);
            $table->index(['customer_id', 'type']);
            $table->index(['outgoing_order_number']);
            $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['customer_id']);
            
            // Drop indexes
            $table->dropIndex(['type', 'company_id']);
            $table->dropIndex(['customer_id', 'type']);
            $table->dropIndex(['outgoing_order_number']);
            $table->dropIndex(['date', 'type']);
            
            // Drop columns
            $table->dropColumn([
                'customer_id',
                'outgoing_order_number',
                'customer_number',
                'customer_name',
                'customer_email',
                'customer_mobile',
                'licensed_operator',
                'date',
                'time',
                'due_date',
                'journal_code',
                'journal_invoice_count',
                'discount_percentage',
                'discount_amount',
                'is_tax_inclusive'
            ]);
        });
    }
};
