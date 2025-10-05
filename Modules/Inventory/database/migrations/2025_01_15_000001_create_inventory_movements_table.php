<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Create Inventory Movement Header Table.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            // ✅ Company and User Information
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // ✅ Movement Information
            $table->string('movement_number')->unique(); // Sequential/unique number
            $table->enum('movement_type', [
                'outbound',      // صادر
                'inbound',       // وارد
                'transfer',      // تحويل
                'manufacturing', // تصنيع
                'inventory_count' // جرد
            ]);

            // ✅ Date and Time (automatic on insert)
            $table->date('movement_date');
            $table->time('movement_time');
            $table->timestamp('movement_datetime'); // Combined datetime

            // ✅ Vendor/Customer References (prepare code without creating tables)
            $table->unsignedBigInteger('vendor_id')->nullable(); // Reference to vendors table
            $table->unsignedBigInteger('customer_id')->nullable(); // Reference to customers table
            $table->string('vendor_name')->nullable(); // Store name if table doesn't exist
            $table->string('customer_name')->nullable(); // Store name if table doesn't exist

            // ✅ Movement Description
            $table->text('movement_description')->nullable();

            // ✅ Invoice References (prepare code without creating tables)
            $table->unsignedBigInteger('inbound_invoice_id')->nullable(); // Reference to purchase invoices
            $table->unsignedBigInteger('outbound_invoice_id')->nullable(); // Reference to sales invoices
            // inbound_invoice_number, outbound_invoice_number removed - available via invoice relationships

            // ✅ Additional Information
            $table->string('user_number')->nullable();
            $table->string('shipment_number')->nullable();
            $table->string('invoice_number')->nullable(); // General invoice number
            $table->string('reference')->nullable();

            // ✅ Warehouse Reference (skip foreign key for now)
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('warehouse_number')->nullable(); // Store for quick access
            $table->string('warehouse_name')->nullable(); // Store for quick access

            // ✅ Status and Control
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->boolean('is_confirmed')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            // ✅ Totals (calculated from movement data)
            $table->decimal('total_quantity', 15, 4)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->integer('total_items')->default(0);

            // ✅ System Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // ✅ Indexes
            $table->index(['company_id', 'movement_type']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'movement_date']);
            $table->index(['movement_number']);
            $table->index(['vendor_id']);
            $table->index(['customer_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
