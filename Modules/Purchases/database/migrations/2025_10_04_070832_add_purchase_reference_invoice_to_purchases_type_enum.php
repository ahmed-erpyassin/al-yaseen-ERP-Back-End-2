<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'purchase_reference_invoice' to the type enum
        DB::statement("ALTER TABLE purchases MODIFY COLUMN type ENUM('quotation', 'order', 'outgoing_order', 'shipment', 'invoice', 'expense', 'return_invoice', 'purchase_reference_invoice') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'purchase_reference_invoice' from the type enum
        DB::statement("ALTER TABLE purchases MODIFY COLUMN type ENUM('quotation', 'order', 'outgoing_order', 'shipment', 'invoice', 'expense', 'return_invoice') NOT NULL");
    }
};
