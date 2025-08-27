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
        Schema::create('outgoing_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outgoing_shipment_id')->constrained('outgoing_shipments')->cascadeOnDelete();

            $table->string('item_number');
            $table->string('item_name');
            $table->string('item_statement', 200);
            $table->decimal('unit', 12, 2)->default(0);
            $table->decimal('quantity', 12, 2)->default(0);
            $table->unsignedBigInteger('warehouse_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outgoing_shipment_items');
    }
};
